<?php
// includes/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * BASIC GAME STATE
 */
if (!isset($_SESSION['player_name'])) {
    $_SESSION['player_name'] = '';
}
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
}
if (!isset($_SESSION['evidence_bag'])) {
    $_SESSION['evidence_bag'] = [];
}
if (!isset($_SESSION['cases_solved'])) {
    $_SESSION['cases_solved'] = [];
}
if (!isset($_SESSION['case_progress'])) {
    $_SESSION['case_progress'] = []; // case_id => 0–100
}
if (!isset($_SESSION['active_case_id'])) {
    $_SESSION['active_case_id'] = null;
}
if (!isset($_SESSION['priority_case'])) {
    $_SESSION['priority_case'] = null;
}
if (!isset($_SESSION['max_level_unlocked'])) {
    $_SESSION['max_level_unlocked'] = 1; // level 1 unlocked by default
}
if (!isset($_SESSION['current_level'])) {
    $_SESSION['current_level'] = 1;
}

/*
 * CASE DEFINITIONS
 * 7 cases, each tied to a level 1–7
 */
$CASES = [
    'L1-A' => [
        'title'   => 'Midnight Store Break-in',
        'level'   => 1,
        'tag'     => 'Evidence-first',
        'summary' => 'Small electronics shop. Broken lock, missing laptops. No witnesses yet.',
    ],
    'L2-A' => [
        'title'   => 'Park Bench Wallet Theft',
        'level'   => 2,
        'tag'     => 'Timeline puzzle',
        'summary' => 'Wallet stolen during a busy evening in the park. Conflicting eyewitness accounts.',
    ],
    'L3-A' => [
        'title'   => 'Dorm Room Vandalism',
        'level'   => 3,
        'tag'     => 'Motive puzzle',
        'summary' => 'Room trashed but nothing major stolen. Possible personal grudge.',
    ],
    'L4-A' => [
        'title'   => 'Office Server Room Breach',
        'level'   => 4,
        'tag'     => 'Digital trail',
        'summary' => 'Logs show access after hours. Only three people had the code.',
    ],
    'L5-A' => [
        'title'   => 'Art Gallery Missing Painting',
        'level'   => 5,
        'tag'     => 'High security',
        'summary' => 'Painting removed without tripping alarms. Cameras mysteriously glitched.',
    ],
    'L6-A' => [
        'title'   => 'Subway Platform Assault',
        'level'   => 6,
        'tag'     => 'Witness chaos',
        'summary' => 'Crowded platform, poor lighting, lots of noise. Many partial testimonies.',
    ],
    'L7-A' => [
        'title'   => 'Rooftop Rendezvous',
        'level'   => 7,
        'tag'     => 'Final case',
        'summary' => 'Meeting on a locked rooftop. One person left, one never made it down.',
    ],
];

/*
 * HELPER FUNCTIONS
 */

function get_active_case()
{
    global $CASES;
    $id = $_SESSION['active_case_id'] ?? null;
    if ($id && isset($CASES[$id])) {
        return ['id' => $id] + $CASES[$id];
    }
    return null;
}

function set_active_case(string $case_id): void
{
    global $CASES;
    if (isset($CASES[$case_id])) {
        $_SESSION['active_case_id'] = $case_id;
        // If no progress yet, start at 10% so bar is visible
        if (!isset($_SESSION['case_progress'][$case_id])) {
            $_SESSION['case_progress'][$case_id] = 10;
        }
    }
}

function get_case_progress(string $case_id): int
{
    return (int)($_SESSION['case_progress'][$case_id] ?? 0);
}

// Increase case progress and unlock next level if needed
function update_case_progress(string $case_id, int $amount): void
{
    if (!isset($_SESSION['case_progress'][$case_id])) {
        $_SESSION['case_progress'][$case_id] = 0;
    }
    $_SESSION['case_progress'][$case_id] = max(
        0,
        min(100, $_SESSION['case_progress'][$case_id] + $amount)
    );

    global $CASES;
    if (
        isset($CASES[$case_id]) &&
        $_SESSION['case_progress'][$case_id] >= 70
    ) {
        $level = (int)$CASES[$case_id]['level'];
        if ($_SESSION['max_level_unlocked'] < $level + 1) {
            $_SESSION['max_level_unlocked'] = min(7, $level + 1);
        }
        $_SESSION['cases_solved'][$case_id] = true;
    }
}

/**
 * NEW: map a level 1–7 to a case id in $CASES
 * This fixes "undefined function get_case_for_level()"
 */
function get_case_for_level(int $level): ?string
{
    global $CASES;
    foreach ($CASES as $id => $case) {
        if ((int)$case['level'] === $level) {
            return $id;
        }
    }
    return null;
}
