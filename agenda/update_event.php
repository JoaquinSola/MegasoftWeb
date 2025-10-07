<?php
header('Content-Type: application/json; charset=utf-8');
include 'db.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = isset($input['action']) ? $input['action'] : null;

if(!$action){ echo json_encode(['success'=>false,'msg'=>'No action']); exit; }

// Ensure `done` column exists
$res = $conn->query("SHOW COLUMNS FROM `eventos` LIKE 'done'");
if($res && $res->num_rows === 0){
    $conn->query("ALTER TABLE `eventos` ADD COLUMN `done` TINYINT(1) NOT NULL DEFAULT 0");
}

// Helper to build WHERE clause and params
function buildWhere($conn, $input){
    if(isset($input['id']) && is_numeric($input['id'])){
        return ["`id` = ?", ['i', (int)$input['id']]];
    }
    if(isset($input['fecha']) && isset($input['titulo'])){
        return ["`fecha` = ? AND `titulo` = ?", ['ss', $input['fecha'], $input['titulo']]];
    }
    return null;
}

$where = buildWhere($conn, $input);
if(!$where){ echo json_encode(['success'=>false,'msg'=>'Missing id or fecha+titulo']); exit; }

if($action === 'mark_done'){
    $sql = "UPDATE `eventos` SET `done` = 1 WHERE " . $where[0] . " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if(!$stmt){ echo json_encode(['success'=>false,'msg'=>'Prepare failed']); exit; }
    if($where[1][0] === 'i'){
        $stmt->bind_param('i', $where[1][1]);
    } else {
        $stmt->bind_param('ss', $where[1][1], $where[1][2]);
    }
    $ok = $stmt->execute();
    $stmt->close();
    if($ok){
        // Fetch updated row
        if(isset($input['id']) && is_numeric($input['id'])){
            $qid = (int)$input['id'];
            $r = $conn->query("SELECT * FROM eventos WHERE id=$qid LIMIT 1");
        } else {
            // use fecha+titulo
            $f = $conn->real_escape_string($input['fecha']);
            $t = $conn->real_escape_string($input['titulo']);
            $r = $conn->query("SELECT * FROM eventos WHERE fecha='$f' AND titulo='$t' LIMIT 1");
        }
        $row = $r ? $r->fetch_assoc() : null;
        echo json_encode(['success'=>true, 'event'=>$row]);
    } else {
        echo json_encode(['success'=>false]);
    }
    exit;
}

if($action === 'change_date'){
    if(empty($input['new_fecha'])){ echo json_encode(['success'=>false,'msg'=>'new_fecha required']); exit; }
    $sql = "UPDATE `eventos` SET `fecha` = ? WHERE " . $where[0] . " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if(!$stmt){ echo json_encode(['success'=>false,'msg'=>'Prepare failed']); exit; }
    if($where[1][0] === 'i'){
        $stmt->bind_param('si', $input['new_fecha'], $where[1][1]);
    } else {
        $stmt->bind_param('sss', $input['new_fecha'], $where[1][1], $where[1][2]);
    }
    $ok = $stmt->execute();
    $stmt->close();
    if($ok){
        // Fetch updated row. Prefer id if provided
        if(isset($input['id']) && is_numeric($input['id'])){
            $qid = (int)$input['id'];
            $r = $conn->query("SELECT * FROM eventos WHERE id=$qid LIMIT 1");
        } else {
            // try to fetch by titulo + new_fecha
            $nf = $conn->real_escape_string($input['new_fecha']);
            $t = isset($input['titulo']) ? $conn->real_escape_string($input['titulo']) : '';
            if($t !== ''){
                $r = $conn->query("SELECT * FROM eventos WHERE titulo='$t' AND fecha='$nf' ORDER BY id DESC LIMIT 1");
            } else {
                $r = null;
            }
        }
        $row = $r ? $r->fetch_assoc() : null;
        echo json_encode(['success'=>true, 'event'=>$row]);
    } else {
        echo json_encode(['success'=>false]);
    }
    exit;
}

echo json_encode(['success'=>false,'msg'=>'Unknown action']);

?>
