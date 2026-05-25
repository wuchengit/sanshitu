<?php
error_log("TEST: Starting...");
$raw = file_get_contents('php://input');
error_log("TEST: raw=" . $raw);
$input = json_decode($raw, true);
error_log("TEST: decoded=" . json_encode($input));
if ($input && $input['name']) {
  echo json_encode(['ok'=>true,'name'=>$input['name']]);
} else {
  echo json_encode(['error'=>'no input']);
}
