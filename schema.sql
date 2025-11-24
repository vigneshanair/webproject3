-- =====================================================================
-- CLEAN SCHEMA FOR CRYPTIC QUEST
-- This will DROP and RECREATE tables used by the game.
-- =====================================================================

-- Make sure we are in your DB
CREATE DATABASE IF NOT EXISTS vajithnair1;
USE vajithnair1;

-- -------------------------
-- DROP old tables if exist
-- -------------------------
DROP TABLE IF EXISTS collected_evidence;
DROP TABLE IF EXISTS interrogations;
DROP TABLE IF EXISTS leaderboard;
DROP TABLE IF EXISTS player_case_stats;
DROP TABLE IF EXISTS case_dependencies;
DROP TABLE IF EXISTS evidence;
DROP TABLE IF EXISTS suspects;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS players;

-- -------------
-- PLAYERS
-- -------------
CREATE TABLE players (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------
-- CASES  (7 interconnected cases)
-- -------------
CREATE TABLE cases (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(150) NOT NULL,
  description  TEXT NOT NULL,
  difficulty   ENUM('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
  order_index  TINYINT UNSIGNED NOT NULL,    -- order 1..7
  active       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed 7 cases in order (titles from your UI)
INSERT INTO cases (id, title, description, difficulty, order_index) VALUES
(1, 'The Vanishing Bracelet',
 'A priceless heirloom disappears during a high-profile gala.',
 'Easy', 1),
(2, 'Whispers in the Alley',
 'A shadowy figure and a missing informant connect back to the gala.',
 'Easy', 2),
(3, 'Echoes in the Gallery',
 'A crime hidden in plain sight among paintings—ties to earlier clues.',
 'Medium', 3),
(4, 'Midnight on Metro Line',
 'A train, a blackout, and a vanished briefcase deepen the conspiracy.',
 'Medium', 4),
(5, 'The Broken Alibi',
 'Everyone has a story. One is a lie that rewrites earlier events.',
 'Medium', 5),
(6, 'Static on Channel 9',
 'A live broadcast hides a deadly secret linked to past suspects.',
 'Hard', 6),
(7, 'The Final Thread',
 'All previous cases collide in one final puzzle that reveals the truth.',
 'Hard', 7);

-- -------------
-- CASE DEPENDENCIES
-- (each case unlocks when previous one is completed)
-- -------------
CREATE TABLE case_dependencies (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  case_id               INT UNSIGNED NOT NULL,
  required_case_id      INT UNSIGNED NOT NULL,
  required_solution_code VARCHAR(50) DEFAULT NULL,
  CONSTRAINT fk_cd_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  CONSTRAINT fk_cd_required_case
    FOREIGN KEY (required_case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO case_dependencies (case_id, required_case_id, required_solution_code) VALUES
(2, 1, NULL),
(3, 2, NULL),
(4, 3, NULL),
(5, 4, NULL),
(6, 5, NULL),
(7, 6, NULL);

-- -------------
-- SUSPECTS
-- -------------
CREATE TABLE suspects (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  case_id     INT UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  role        VARCHAR(100) DEFAULT NULL,
  personality VARCHAR(255) DEFAULT NULL,
  bio         TEXT,
  is_primary  TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_suspect_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed suspects for CASE 1 (you can add more for other cases)
INSERT INTO suspects (case_id, name, role, personality, bio, is_primary) VALUES
(1, 'Lena Hart',  'Personal Assistant', 'Organised, Nervous',
 'Worked closely with the victim, knows their schedule better than anyone.', 1),
(1, 'Marco Vance','Security Guard',     'Charming, Evasive',
 'On duty during the gala, always “around” but never where the cameras see him.', 0),
(1, 'Iris Cole',  'Art Dealer',         'Calm, Observant',
 'Long-time family friend who understands the value of the heirloom.', 0);

-- -------------
-- INTERROGATIONS
-- Each row is one lead (good, bad, dead-end, truth/lie)
-- -------------
CREATE TABLE interrogations (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  case_id     INT UNSIGNED NOT NULL,
  suspect_id  INT UNSIGNED NOT NULL,
  question    VARCHAR(255) NOT NULL,
  answer      TEXT NOT NULL,
  lead_type   ENUM('good','bad','dead_end') NOT NULL DEFAULT 'dead_end',
  is_truth    TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_int_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  CONSTRAINT fk_int_suspect
    FOREIGN KEY (suspect_id) REFERENCES suspects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample interrogation tree for CASE 1 / Lena (id will be 1 because we just inserted)
INSERT INTO interrogations (case_id, suspect_id, question, answer, lead_type, is_truth) VALUES
(1, 1, 'Where were you during the theft?',
 'I was coordinating the guest list near the entrance. I never left my post.', 'bad', 0),
(1, 1, 'How well did you know the victim?',
 'I organised their entire life. If they sneezed, I had tissues ready.', 'good', 1),
(1, 1, 'Who else had access to the bracelet?',
 'The guards and a few VIP guests, but I never touched the display.', 'good', 0),

(1, 2, 'Did you see anyone near the display?',
 'Lots of people passed by. Hard to keep track during the toast.', 'dead_end', 1),
(1, 2, 'Why are there gaps in your patrol log?',
 'System glitch. The cameras keep freezing during big events.', 'bad', 0),

(1, 3, 'Why was the bracelet so valuable?',
 'Its value isn''t just money. Some people would kill for what it represents.', 'good', 1),
(1, 3, 'Did you argue with the victim?',
 'Disagreements happen in business. That doesn''t make me a thief.', 'dead_end', 1);

-- -------------
-- EVIDENCE MASTER
-- -------------
CREATE TABLE evidence (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  case_id      INT UNSIGNED NOT NULL,
  label        VARCHAR(255) NOT NULL,
  description  TEXT,
  lead_type    ENUM('good','bad','dead_end') NOT NULL DEFAULT 'dead_end',
  is_critical  TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_evidence_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Evidence for CASE 1 (good + bad + dead ends)
INSERT INTO evidence (case_id, label, description, lead_type, is_critical) VALUES
(1, 'Gala invitation with suspicious timestamp',
 'Invitation timestamp doesn''t match the announced start time.', 'good', 0),
(1, 'Smudged footprints near the open window',
 'Prints leading away from the display, size matches security boots.', 'good', 1),
(1, 'Broken bracelet clasp under the table',
 'Clasp appears forced, not accidentally broken.', 'good', 1),
(1, 'Receipt with wrong time',
 'Restaurant bill with a time that contradicts Lena''s claimed alibi.', 'bad', 1),
(1, 'Crumpled staff memo',
 'Generic memo about staffing, looks important but leads nowhere.', 'dead_end', 0);

-- -------------
-- COLLECTED EVIDENCE (evidence bag)
-- -------------
CREATE TABLE collected_evidence (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id    INT UNSIGNED NOT NULL,
  case_id      INT UNSIGNED NOT NULL,
  evidence_id  INT UNSIGNED NOT NULL,
  collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ce_player
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_ce_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  CONSTRAINT fk_ce_evidence
    FOREIGN KEY (evidence_id) REFERENCES evidence(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------
-- PLAYER / CASE STATS for dynamic difficulty & solutions
-- -------------
CREATE TABLE player_case_stats (
  id                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id              INT UNSIGNED NOT NULL,
  case_id                INT UNSIGNED NOT NULL,
  attempts               INT UNSIGNED NOT NULL DEFAULT 0,
  correct_interrogations INT UNSIGNED NOT NULL DEFAULT 0,
  wrong_accusations      INT UNSIGNED NOT NULL DEFAULT 0,
  solution_code          VARCHAR(50) DEFAULT NULL,
  score                  INT NOT NULL DEFAULT 0,
  time_started           TIMESTAMP NULL DEFAULT NULL,
  time_finished          TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_player_case (player_id, case_id),
  CONSTRAINT fk_pcs_player
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
  CONSTRAINT fk_pcs_case
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------
-- LEADERBOARD
-- -------------
CREATE TABLE leaderboard (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id        INT UNSIGNED NOT NULL,
  total_score      INT NOT NULL DEFAULT 0,
  cases_solved     INT NOT NULL DEFAULT 0,
  avg_time_seconds INT NOT NULL DEFAULT 0,
  last_updated     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                     ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lb_player
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
