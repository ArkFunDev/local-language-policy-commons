# System Architecture - Local Language Policy Commons

## 1. Architecture Overview

Local Language Policy Commons is planned as a modular web platform connecting BASAibu's MediaWiki infrastructure with multilingual proposal exploration and community review workflows.

The architecture prioritizes interoperability, transparency, data integrity, and human oversight.

## 2. Main Components

### A. MediaWiki Integration
- Connect to the MediaWiki API
- Retrieve community generated policy proposals
- Preserve original text, language information, and source references

### B. PHP Backend
- Handle application requests and business logic
- Process proposal data
- Manage user interactions and review workflows
- Communicate with external APIs

### C. MySQL Database
- Store synchronized proposal metadata
- Maintain topic relationships and review records
- Track corrections and community feedback

### D. Multilingual Processing
- Support language identification and text exploration
- Suggest potentially related proposals using topic similarity
- Preserve original text alongside any generated assistance

### E. Human Review Interface
- Display automated similarity suggestions
- Allow community reviewers to confirm, reject, or correct suggestions
- Record review decisions and feedback

## 3. Data Flow

1. The platform requests proposal data from MediaWiki
2. The PHP backend validates and processes the retrieved data
3. Relevant proposal metadata is stored in MySQL
4. Multilingual processing generates exploratory topic similarity suggestions
5. Users explore original proposals and related content
6. Community reviewers validate or correct suggested groupings
7. Approved review decisions are recorded for future reference

## 4. Simplified Architecture

MediaWiki API  
     ↓  
PHP Backend  
     ↓  
MySQL Database  
     ↓  
Multilingual Processing  
     ↓  
Similarity Suggestions  
     ↓  
Community Review Interface  
     ↓  
Human Validated Topic Groupings

## 5. Data and AI Governance

- Original language content must remain accessible
- Automated suggestions must be clearly identified
- AI must not independently rank or determine policy priorities
- Human reviewers retain authority over corrections and validation
- Data collection and processing must respect community consent and privacy
- Source references and review history should remain traceable

## 6. Initial Technology Stack

- Backend : PHP
- Database : MySQL
- Integration : MediaWiki API and REST API
- Frontend : HTML, CSS, JavaScript
- Processing : Multilingual text processing and semantic similarity tools

Technology choices will be refined through technical research and collaboration with BASAibu.

## 7. Development Status

**Status:** Proposed architecture - early design stage.

This document describes the intended architecture. Implementation details will be validated during research, prototyping, and collaboration with BASAibu.

## 8. Future Improvements

- Expand support for Indonesian local languages
- Improve multilingual similarity evaluation
- Introduce transparent community moderation tools
- Develop reusable integration components
- Publish technical documentation for other civic technology initiatives
