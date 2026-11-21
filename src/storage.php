<?php
/**
 * File-based JSON storage with locking.
 *
 * This is intentionally simple for the prototype. See docs/SYSTEM-ARCHITECTURE.md
 * and docs/schema.mysql.sql for the planned database-backed storage layer.
 */

function llpc_storage_dir()
{
    $dir = rtrim((string) llpc_config('storage_dir'), '/\\');
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

function llpc_storage_path($name)
{
    return llpc_storage_dir() . '/' . $name;
}

function llpc_read_json($file, $default = [])
{
    if (!is_file($file)) {
        return $default;
    }
    $raw = @file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

/**
 * Atomic read-modify-write of a JSON file under an exclusive lock.
 *
 * The callback receives the current data and must return the new data,
 * or false to abort without writing. If the existing file is non-empty but
 * is not valid JSON, nothing is written (so corrupt data is never overwritten).
 *
 * @return bool true if the new data was written.
 */
function llpc_update_json($file, $callback, $default = [])
{
    $handle = @fopen($file, 'c+');
    if (!$handle) {
        return false;
    }
    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $raw  = stream_get_contents($handle);
    $data = $default;
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return false;
        }
        $data = $decoded;
    }

    $new = call_user_func($callback, $data);
    if ($new === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $json = json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    rewind($handle);
    ftruncate($handle, 0);
    $written = fwrite($handle, $json);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $written !== false;
}

function llpc_new_id($prefix)
{
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Append-only audit trail for moderator actions (one JSON object per line).
 * Never log passwords, IP addresses or free-text user content here.
 */
function llpc_audit($action, $target = '', $detail = '')
{
    $line = json_encode([
        'at'     => date('c'),
        'action' => (string) $action,
        'target' => (string) $target,
        'detail' => (string) $detail,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($line !== false) {
        @file_put_contents(llpc_storage_path('audit.log'), $line . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Per-installation secret used to HMAC rate-limit keys, so raw IP addresses are never stored.
 */
function llpc_secret()
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $file = llpc_storage_path('secret.key');
    $raw  = is_file($file) ? trim((string) @file_get_contents($file)) : '';
    if (strlen($raw) < 32) {
        $raw = bin2hex(random_bytes(32));
        @file_put_contents($file, $raw, LOCK_EX);
        @chmod($file, 0600);
    }
    $secret = $raw;
    return $secret;
}

function llpc_client_ip()
{
    if (PHP_SAPI === 'cli') {
        return 'cli';
    }
    if (llpc_config('trust_proxy_headers') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip    = trim($parts[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
}

/**
 * Sliding-window rate limit. Returns true when the action is allowed.
 * With $record = false the attempt is only checked, not counted.
 */
function llpc_rate_allow($action, $limit, $window, $record = true)
{
    $key     = hash_hmac('sha256', llpc_client_ip() . '|' . $action, llpc_secret());
    $now     = time();
    $allowed = true;

    llpc_update_json(llpc_storage_path('ratelimit.json'), function ($data) use ($key, $limit, $window, $now, $record, &$allowed) {
        foreach ($data as $k => $stamps) {
            $fresh = [];
            foreach ((array) $stamps as $t) {
                if (($now - (int) $t) < 86400) {
                    $fresh[] = (int) $t;
                }
            }
            if ($fresh) {
                $data[$k] = $fresh;
            } else {
                unset($data[$k]);
            }
        }

        $recent = 0;
        if (isset($data[$key])) {
            foreach ($data[$key] as $t) {
                if (($now - $t) < $window) {
                    $recent++;
                }
            }
        }

        if ($recent >= $limit) {
            $allowed = false;
            return $data;
        }
        if ($record) {
            $data[$key][] = $now;
        }
        return $data;
    }, []);

    return $allowed;
}

function llpc_rate_record($action)
{
    llpc_rate_allow($action, PHP_INT_MAX, 86400, true);
}
