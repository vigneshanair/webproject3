<?php
// session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['evidence_bag'])) {
    $_SESSION['evidence_bag'] = [];  // [caseId => [itemId => ['label'=>..., 'time'=>...]]]
}

if (!isset($_SESSION['notebook'])) {
    $_SESSION['notebook'] = [];      // [caseId => text]
}

function add_evidence($itemId, $label, $caseId) {
    if (!isset($_SESSION['evidence_bag'][$caseId])) {
        $_SESSION['evidence_bag'][$caseId] = [];
    }
    if (!isset($_SESSION['evidence_bag'][$caseId][$itemId])) {
        $_SESSION['evidence_bag'][$caseId][$itemId] = [
            'label' => $label,
            'time'  => date('Y-m-d H:i:s')
        ];
    }
}

function get_evidence_for_case($caseId) {
    return $_SESSION['evidence_bag'][$caseId] ?? [];
}

function set_notebook($caseId, $text) {
    $_SESSION['notebook'][$caseId] = $text;
}

function get_notebook($caseId) {
    return $_SESSION['notebook'][$caseId] ?? '';
}
