<?php
session_start();
require_once "db.php";

$subject_tables = [
    "Histoire-Géo-EMC" => "histoire_geo_emc",
    "Anglais" => "anglais",
    "Allemand" => "allemand",
    "SVT" => "svt",
    "Sciences Physiques" => "sciences_physiques",
    "Mathématiques" => "mathematiques",
    "Français" => "francais"
];

function resp($ok, $data = []) {
    header("Content-Type: application/json");
    echo json_encode(array_merge(["ok" => $ok], $data));
    exit;
}

function validate_table_name($table, $subject_tables) {
    return in_array($table, array_values($subject_tables)) ? $table : false;
}

$action = $_GET["action"] ?? "";

switch ($action) {
    case "list_subjects":
        resp(true, ["subjects" => array_keys($subject_tables)]);
        break;

    case "list_pairs":
        $subject = $_GET["subject"] ?? "";
        if (!isset($subject_tables[$subject])) resp(false, ["error" => "Invalid subject"]);
        $table = validate_table_name($subject_tables[$subject], $subject_tables);
        if (!$table) resp(false, ["error" => "Invalid table"]);
        
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY RAND()");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        resp(true, ["pairs" => $rows]);
        break;

    case "add_pair":
        $subject = $_POST["subject"] ?? "";
        $q = trim($_POST["question"] ?? "");
        $a = trim($_POST["answer"] ?? "");
        if (!isset($subject_tables[$subject]) || !$q || !$a) resp(false, ["error" => "Missing data"]);
        $table = validate_table_name($subject_tables[$subject], $subject_tables);
        if (!$table) resp(false, ["error" => "Invalid table"]);
        
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO `$table` (question,answer) VALUES (?,?)");
        $stmt->execute([$q, $a]);
        resp(true, ["id" => $pdo->lastInsertId()]);
        break;

    case "update_pair":
        $subject = $_POST["subject"] ?? "";
        $id = intval($_POST["id"] ?? 0);
        $q = trim($_POST["question"] ?? "");
        $a = trim($_POST["answer"] ?? "");
        
        if (!isset($subject_tables[$subject]) || !$id || !$q || !$a) {
            resp(false, ["error" => "Données manquantes"]);
        }
        
        $table = validate_table_name($subject_tables[$subject], $subject_tables);
        if (!$table) resp(false, ["error" => "Invalid table"]);
        
        global $pdo;
        $stmt = $pdo->prepare("UPDATE `$table` SET question = ?, answer = ? WHERE id = ?");
        $stmt->execute([$q, $a, $id]);
        
        if ($stmt->rowCount() > 0) {
            resp(true);
        } else {
            resp(false, ["error" => "Aucune modification effectuée"]);
        }
        break;

    case "delete_pair":
        $subject = $_POST["subject"] ?? "";
        $id = intval($_POST["id"] ?? 0);
        if (!isset($subject_tables[$subject]) || !$id) resp(false, ["error" => "Invalid data"]);
        $table = validate_table_name($subject_tables[$subject], $subject_tables);
        if (!$table) resp(false, ["error" => "Invalid table"]);
        
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id=?");
        $stmt->execute([$id]);
        resp(true);
        break;

    case "save_score":
        $subject = $_POST["subject"] ?? "";
        $score = intval($_POST["score"] ?? 0);
        $total = intval($_POST["total"] ?? 0);
        $time_seconds = intval($_POST["time_seconds"] ?? 0);
        
        if (!$subject || $total <= 0) {
            resp(false, ["error" => "Données manquantes"]);
        }
        
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO game_scores (subject, score, total, time_seconds) VALUES (?, ?, ?, ?)");
        $stmt->execute([$subject, $score, $total, $time_seconds]);
        
        resp(true, ["id" => $pdo->lastInsertId()]);
        break;

    case "get_scores":
        $subject = $_GET["subject"] ?? "";
        
        global $pdo;
        if ($subject) {
            $stmt = $pdo->prepare("SELECT * FROM game_scores WHERE subject = ? ORDER BY created_at DESC");
            $stmt->execute([$subject]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM game_scores ORDER BY created_at DESC");
            $stmt->execute();
        }
        
        $scores = $stmt->fetchAll();
        resp(true, ["scores" => $scores]);
        break;

    default:
        resp(false, ["error" => "Action non reconnue"]);
        break;
}