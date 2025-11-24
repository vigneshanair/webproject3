<?php
// case_data.php
// Narrative data + helper functions for cases, leads, suspects, interrogations.

// ---------- CASES (7 interconnected) ----------
$CASES = [
    1 => [
        'id' => 1,
        'code' => 'CASE01',
        'title' => 'The Vanishing Bracelet',
        'difficulty' => 'Easy',
        'summary' => 'A priceless heirloom disappears during a high-profile gala.',
    ],
    2 => [
        'id' => 2,
        'code' => 'CASE02',
        'title' => 'Whispers in the Alley',
        'difficulty' => 'Easy',
        'summary' => 'A shadowy informant vanishes after hinting at an inside job.',
    ],
    3 => [
        'id' => 3,
        'code' => 'CASE03',
        'title' => 'Echoes in the Gallery',
        'difficulty' => 'Medium',
        'summary' => 'A painting swap points back to the gala guest list.',
    ],
    4 => [
        'id' => 4,
        'code' => 'CASE04',
        'title' => 'Midnight on Metro Line',
        'difficulty' => 'Medium',
        'summary' => 'A blackout on the train hides a critical handoff.',
    ],
    5 => [
        'id' => 5,
        'code' => 'CASE05',
        'title' => 'The Broken Alibi',
        'difficulty' => 'Medium',
        'summary' => 'Conflicting timelines expose someone’s carefully built story.',
    ],
    6 => [
        'id' => 6,
        'code' => 'CASE06',
        'title' => 'Static on Channel 9',
        'difficulty' => 'Hard',
        'summary' => 'A live broadcast hides an encoded warning.',
    ],
    7 => [
        'id' => 7,
        'code' => 'CASE07',
        'title' => 'The Final Thread',
        'difficulty' => 'Hard',
        'summary' => 'All the previous cases collide in one final reveal.',
    ],
];

// ---------- SUSPECTS ----------
/*
 * Each suspect:
 *   id          : unique per case
 *   name        : display name
 *   role        : short title
 *   traits      : flavour text
 *   is_primary  : 1 if they are the true culprit for that case
 */

$SUSPECTS = [
    1 => [ // CASE 1
        [
            'id' => 1,
            'name' => 'Lena Hart',
            'role' => 'Personal Assistant',
            'traits' => 'Organised, nervous',
            'is_primary' => 1, // true culprit in case 1
        ],
        [
            'id' => 2,
            'name' => 'Marco Vance',
            'role' => 'Security Guard',
            'traits' => 'Charming, evasive',
            'is_primary' => 0,
        ],
        [
            'id' => 3,
            'name' => 'Iris Cole',
            'role' => 'Art Dealer',
            'traits' => 'Observant, calm',
            'is_primary' => 0,
        ],
    ],
    // You can add suspects for cases 2-7 later if needed,
    // but the helpers below will still work.
];

// ---------- CRIME-SCENE LEADS ----------
// good_or_bad: 'good' (moves case forward) or 'dead_end' (false lead)
// evidence_label: what goes into the evidence bag text.

$LEADS = [
    1 => [ // CASE 1
        [
            'key' => 'desk_invite',
            'zone' => 'Search the Desk',
            'title' => 'Gala Invitation with Odd Time Stamp',
            'description' => 'The printed time doesn’t match the official gala schedule.',
            'good_or_bad' => 'good',
            'evidence_label' => 'Gala invitation with suspicious time stamp',
        ],
        [
            'key' => 'window_smudge',
            'zone' => 'Check the Window',
            'title' => 'Smudged Footprints by Window',
            'description' => 'Footprints suggest someone left in a hurry.',
            'good_or_bad' => 'good',
            'evidence_label' => 'Smudged footprints near the open window',
        ],
        [
            'key' => 'floor_glass',
            'zone' => 'Inspect the Floor',
            'title' => 'Tiny Glass Shards',
            'description' => 'Shards from the display case lie under the table.',
            'good_or_bad' => 'good',
            'evidence_label' => 'Broken bracelet clasp under the table',
        ],
        [
            'key' => 'coatroom_receipt',
            'zone' => 'Check the Coatroom',
            'title' => 'Crinkled Receipt',
            'description' => 'Looks interesting but turns out to be from a week ago.',
            'good_or_bad' => 'dead_end',
            'evidence_label' => 'Old receipt – irrelevant to the night of the gala',
        ],
        [
            'key' => 'flower_pollen',
            'zone' => 'Examine the Flowers',
            'title' => 'Pollen on the Carpet',
            'description' => 'A messy flower arrangement that leads nowhere.',
            'good_or_bad' => 'dead_end',
            'evidence_label' => 'Pollen trail – likely from staff prep',
        ],
    ],
];

// ---------- INTERROGATION QUESTION TREES ----------
// Each question is either helpful or a dead end.
// type: 'truth' (gives you good info), 'lie' (misleading), 'mixed'
// dead_end: 1 means story branch is basically useless.

$INTERROGATIONS = [
    1 => [ // CASE 1
        1 => [ // Lena Hart (primary)
            [
                'key' => 'motivation',
                'question' => 'Why would anyone want to steal the bracelet?',
                'answer'   => 'Lena’s voice cracks as she says: "You don’t understand what they did to me."',
                'type'     => 'truth',
                'dead_end' => 0,
                'adds_evidence' => 'Lena Hart shows strong resentment towards the victim.',
            ],
            [
                'key' => 'timeline',
                'question' => 'Where were you during the theft?',
                'answer'   => 'She insists she was coordinating staff, but her timing doesn’t line up with the gala schedule.',
                'type'     => 'mixed',
                'dead_end' => 0,
                'adds_evidence' => 'Lena Hart’s stated timeline doesn’t match the gala invitation time.',
            ],
            [
                'key' => 'smalltalk',
                'question' => 'Did you enjoy the food at the gala?',
                'answer'   => 'She gives a long rant about the catering. It tells you nothing.',
                'type'     => 'dead',
                'dead_end' => 1,
                'adds_evidence' => null,
            ],
        ],
        2 => [ // Marco Vance
            [
                'key' => 'patrol_route',
                'question' => 'Walk me through your patrol route.',
                'answer'   => 'Marco quickly lists checkpoints, but skips the gallery wing entirely.',
                'type'     => 'truth',
                'dead_end' => 0,
                'adds_evidence' => 'Marco Vance omits the gallery wing from his patrol route.',
            ],
            [
                'key' => 'badge_logs',
                'question' => 'Why do badge logs show a gap during the theft?',
                'answer'   => 'He laughs it off as a "system glitch".',
                'type'     => 'lie',
                'dead_end' => 0,
                'adds_evidence' => 'Security logs show a suspicious gap during the theft.',
            ],
            [
                'key' => 'gossip',
                'question' => 'Hear any good gossip from guests?',
                'answer'   => 'He starts telling dramatic stories that clearly sound made up.',
                'type'     => 'dead',
                'dead_end' => 1,
                'adds_evidence' => null,
            ],
        ],
        3 => [ // Iris Cole
            [
                'key' => 'gallery',
                'question' => 'What did you notice in the gallery before the theft?',
                'answer'   => 'Iris calmly lists guests near the display, including Lena lingering alone.',
                'type'     => 'truth',
                'dead_end' => 0,
                'adds_evidence' => 'Witness places Lena Hart alone near the bracelet before the theft.',
            ],
            [
                'key' => 'art_value',
                'question' => 'How valuable is the bracelet, really?',
                'answer'   => 'She gives a technical answer about appraisals—interesting but not case-breaking.',
                'type'     => 'mixed',
                'dead_end' => 1,
                'adds_evidence' => null,
            ],
        ],
    ],
];

// ---------- HELPER FUNCTIONS ----------

function cq_get_case(int $caseId) {
    global $CASES;
    return $CASES[$caseId] ?? null;
}

function get_leads_for_case(int $caseId): array {
    global $LEADS;
    return $LEADS[$caseId] ?? [];
}

function find_lead_by_key(int $caseId, string $key): ?array {
    $leads = get_leads_for_case($caseId);
    foreach ($leads as $lead) {
        if ($lead['key'] === $key) return $lead;
    }
    return null;
}

function get_suspects_for_case(int $caseId): array {
    global $SUSPECTS;
    return $SUSPECTS[$caseId] ?? [];
}

function get_suspect_for_case(int $caseId, int $suspectId): ?array {
    $suspects = get_suspects_for_case($caseId);
    foreach ($suspects as $s) {
        if ($s['id'] === $suspectId) return $s;
    }
    return null;
}

function get_questions_for_suspect(int $caseId, int $suspectId): array {
    global $INTERROGATIONS;
    return $INTERROGATIONS[$caseId][$suspectId] ?? [];
}
