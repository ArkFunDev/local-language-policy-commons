-- PLANNED relational schema for Local Language Policy Commons.
-- NOT used by the v0.2 prototype (which stores JSON files). Provided as a design reference
-- for the Q2 database-backed storage layer. Draft only; will change after research with BASAibu.

CREATE TABLE languages (
  code        VARCHAR(8)   PRIMARY KEY,           -- ISO 639 code
  name        VARCHAR(80)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE topics (
  slug        VARCHAR(40)  PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proposals (
  id                   CHAR(12)     PRIMARY KEY,   -- e.g. SUB-1A2B3C4D
  language_code        VARCHAR(8)   NOT NULL,
  language_name        VARCHAR(80)  NOT NULL,
  original_text        TEXT         NOT NULL,      -- never modified after submission
  indonesian_gloss     TEXT         NOT NULL,
  topic_slug           VARCHAR(40)  NOT NULL,
  source_type          ENUM('community','mediawiki','other') NOT NULL DEFAULT 'community',
  source_url           VARCHAR(1000) NULL,
  attribution          VARCHAR(200) NULL,          -- pseudonym allowed
  verification_status  ENUM('pending','verified','needs_clarification') NOT NULL DEFAULT 'pending',
  language_verified    TINYINT(1)   NOT NULL DEFAULT 0,
  review_status        ENUM('unreviewed','accepted','needs_revision') NOT NULL DEFAULT 'unreviewed',
  license_consent      TINYINT(1)   NOT NULL DEFAULT 0,
  is_synthetic         TINYINT(1)   NOT NULL DEFAULT 0,
  submitted_at         DATETIME     NOT NULL,
  reviewed_at          DATETIME     NULL,
  verified_at          DATETIME     NULL,
  FOREIGN KEY (language_code) REFERENCES languages(code),
  FOREIGN KEY (topic_slug)    REFERENCES topics(slug),
  KEY idx_review (review_status),
  FULLTEXT KEY ft_text (original_text, indonesian_gloss)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedback (
  id           CHAR(11)     PRIMARY KEY,
  proposal_id  CHAR(12)     NOT NULL,
  kind         ENUM('comment','language_correction','context_note') NOT NULL,
  content      TEXT         NOT NULL,
  status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at   DATETIME     NOT NULL,
  moderated_at DATETIME     NULL,
  FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suggested pairs and the human decision about them. Method + version keep suggestions auditable
-- and let different similarity methods be compared in the evaluation phase.
CREATE TABLE similarity_pairs (
  proposal_a   CHAR(12)     NOT NULL,
  proposal_b   CHAR(12)     NOT NULL,              -- store with proposal_a < proposal_b
  method       VARCHAR(40)  NOT NULL,              -- e.g. 'word-overlap-v1'
  score        DECIMAL(5,2) NOT NULL,
  review       ENUM('unreviewed','confirmed','rejected') NOT NULL DEFAULT 'unreviewed',
  reviewed_at  DATETIME     NULL,
  PRIMARY KEY (proposal_a, proposal_b, method),
  FOREIGN KEY (proposal_a) REFERENCES proposals(id) ON DELETE CASCADE,
  FOREIGN KEY (proposal_b) REFERENCES proposals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moderators (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  display_name  VARCHAR(80)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('moderator','language_reviewer','admin') NOT NULL DEFAULT 'moderator',
  created_at    DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  at          DATETIME     NOT NULL,
  moderator_id INT UNSIGNED NULL,
  action      VARCHAR(60)  NOT NULL,
  target      VARCHAR(80)  NULL,
  detail      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
