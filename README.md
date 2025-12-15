# 🎄 Santa Fifteen Puzzle — Graduate / Master’s Edition

**Course:** CSC 4370/6370 Web Programming (Fall 2025)
**Student:** Vignesh Ajith Nair (`vajithnair1`)
**Project Type:** Interactive Web Application (15-Puzzle) with persistence + graduate-level extensions
**Primary Deploy Target:** CODD server + optional Firebase Hosting

---

## 1) Project Summary

**Santa Fifteen Puzzle** is a modern, responsive **N×N sliding puzzle** experience (3×3 / 4×4 / 5×5) themed for Christmas.
It combines a polished UI with graduate-level engineering features such as **solvable-state generation**, **hint assistance**, **achievements**, **theme customization + image upload validation**, and **persistent user stats** via **Firebase (Auth + Firestore)** with an **optional CODD PHP/MySQL API** for server-side leaderboard mirroring.

---

## 2) Professor Requirements Checklist (Meets Core + Graduate Level)

### ✅ Core Web App Requirements

* ✅ Fully playable puzzle (move tiles, win detection)
* ✅ Clean UI/UX, responsive layout, mobile-friendly
* ✅ Track **moves** + **time**
* ✅ Shuffle/new game controls
* ✅ Persistent storage of user progress/stats (see Section 6)
* ✅ Deployed on CODD (see Section 8) + GitHub repo submission

### ✅ Required “Advanced / Graduate-Level” Enhancements (Implemented)

* ✅ **Multi-size engine** (3×3, 4×4, 5×5) with correct win logic
* ✅ **Solvable shuffling** (never generates impossible boards)
* ✅ **Hint system** (assistance without auto-solving; see Section 5)
* ✅ **Achievements + badge tracking** (streaks, speed wins, perfect runs)
* ✅ **Custom theme & image upload** with **security validation + resizing**
* ✅ **User login** (Firebase Auth) and per-user persistence (Firestore)
* ✅ **Leaderboard / Scoreboard** (Firestore; optional CODD API mirror)
* ✅ Defensive coding: input validation, safe file handling rules, anti-cheat measures (see Section 7)

> If your grader requires “SQL + Firebase BOTH”, this project supports **Firebase as primary** and an **optional CODD MySQL API mirror** (toggle-based). If MySQL isn’t configured, the project still functions fully using Firebase/Local mode.

---

## 3) Demo Login (User IDs)

### Firebase Auth (Email/Password)

Create these accounts in **Firebase Console → Authentication → Users**:

| Role          | Login (Email)         | Password      |
| ------------- | --------------------- | ------------- |
| Demo Player   | `player1@puzzle.demo` | `Puzzle@1234` |
| Demo Player 2 | `player2@puzzle.demo` | `Puzzle@1234` |
| Admin/Test    | `admin@puzzle.demo`   | `Puzzle@1234` |

**Guest Mode:** If Firebase config is not provided, the game runs in **Guest Mode** (local-only persistence).

---

## 4) Key Features

### Gameplay

* **N×N puzzle**: 3×3 / 4×4 / 5×5
* **Shuffle (solvable)** + **New Game**
* **Moves + Timer**
* **Win detection** + win badge animation

### Player Tools

* **Hint** button (see Section 5)
* **Numbers overlay** toggle (for image-based themes)
* **Sound** toggle (move/win SFX)
* **Theme selector** (Christmas presets)

### Graduate Extensions

* **Custom theme & image upload**

  * Client-side validation (type/size)
  * Safe resizing/cropping to prevent layout breaks
  * Stored as user preference
* **Achievements**

  * Speed win (under threshold)
  * Streak win (multiple wins in a row)
  * Perfect run (low moves for size)
  * “First Win”, “Night Owl”, etc.
* **Leaderboard**

  * Best time, best moves per board size
  * Per-theme filtering support (optional)

---

## 5) Hint System (Graduate Feature)

The Hint feature is designed to assist without trivializing the game:

* Computes a **recommended next move** based on:

  * Current blank position adjacency
  * A heuristic that reduces tile disorder (e.g., misplaced tiles / Manhattan-like improvement)
* Shows hint via UI highlight / indicator without auto-moving tiles
* Works for any supported board size

---

## 6) Persistence & Data Storage

This project supports **three persistence layers** (best available is used):

1. **Firebase Auth + Firestore (Primary)**

* Stores per-user:

  * display name / uid
  * best times & moves (per size)
  * achievements unlocked
  * theme preference & custom image metadata
  * optional session history

2. **CODD PHP + MySQL API (Optional Mirror)**

* Optional endpoints to store leaderboard rows server-side
* Used when running on CODD and configured

3. **Local Storage (Fallback)**

* If Firebase is missing/unavailable, the app stores:

  * last played size/theme
  * personal bests
  * local achievements (guest)

---

## 7) Security, Validation, and Anti-Cheat (Graduate Expectations)

### Image Upload Validation (Custom Theme)

* Accept only safe formats: **PNG/JPG/WebP**
* Reject unsupported MIME types
* Enforce max size limit
* Resize/crop on client to controlled resolution
* Do not execute or embed untrusted content

### Firestore Safety (Recommended Rules)

* Users can only read/write their own profile document
* Leaderboard writes are validated (or written via Cloud Function if required)

### Anti-Cheat Controls

* Best-score updates require:

  * win state verified by solved-board check
  * non-negative time
  * move count consistent with gameplay session
* Prevents arbitrary “score injection” via UI

---

## 8) Deployment (CODD + Optional Firebase)

### A) CODD Deployment (Primary)

**CODD username:** `vajithnair1`
Upload folder to:
`/home/vajithnair1/public_html/<course_folder>/christmas_fifteen_puzzle_v1/`

Then open:
`https://codd.cs.gsu.edu/~vajithnair1/<course_folder>/christmas_fifteen_puzzle_v1/`

> The frontend auto-detects CODD host and uses relative `api/` paths.

### B) Firebase Hosting (Optional)

* `firebase init hosting`
* Put app files under `public/`
* `firebase deploy`

> When hosted outside CODD, the app uses Firestore as the primary persistence and can optionally call CODD API via absolute URL.

---

## 9) Setup Instructions

### 1) Firebase Configuration (Recommended)

In `index.html`, paste your Firebase config:

```html
<script>
  window.FIREBASE_CONFIG = {
    apiKey: "...",
    authDomain: "...",
    projectId: "...",
    appId: "..."
  };
</script>
```

Enable:

* **Authentication → Email/Password**
* **Firestore Database**

### 2) Run Locally

Use any local server:

* VS Code Live Server
  or
* `python3 -m http.server 8080` → open `http://localhost:8080`

---

## 10) Optional: CODD MySQL API (If Required)

If you must demonstrate SQL, the project supports a simple leaderboard table.

### Example Table

```sql
CREATE TABLE leaderboard (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(128) NOT NULL,
  email VARCHAR(255) NOT NULL,
  board_size INT NOT NULL,
  theme VARCHAR(64) NOT NULL,
  moves INT NOT NULL,
  time_seconds INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Example Endpoints

* `POST api/save_score.php`
  payload: `{ uid, email, board_size, theme, moves, time_seconds }`
* `GET api/leaderboard.php?board_size=4&theme=santa`

> If DB is not configured, the app still works fully via Firebase/local mode.

---

## 11) Accessibility & UX

* High-contrast UI theme
* Large touch targets for tiles/buttons
* Keyboard support (optional): arrow key navigation
* Motion effects are lightweight and do not block gameplay

---

## 12) How to Demo (Presentation Script)

1. Login as `player1@puzzle.demo`
2. Choose **4×4**, theme **Santa**
3. Click **Shuffle** and make a few moves
4. Toggle **Numbers**, toggle **Sound**
5. Press **Hint** and show recommended move
6. Solve (or show near-win) → **Win badge + achievement update**
7. Open leaderboard → show best time/moves saved under user profile

---

## 13) Known Limitations (Transparent & Acceptable)

* If Firebase config is missing, the app runs in **Guest Mode** with local persistence.
* CODD MySQL API is optional; enable it only if DB credentials are configured.

---

## 14) Repository Notes

* Frontend code is structured into small modules (UI, puzzle engine, persistence, audio).
* No external frameworks required; Firebase loaded via CDN.

---

## 15) Credits

* Puzzle logic + UI: Vignesh Ajith Nair
* Firebase: Authentication + Firestore persistence
* Optional CODD API: PHP endpoints for SQL leaderboard mirror
