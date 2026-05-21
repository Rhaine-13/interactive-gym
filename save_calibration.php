<?php
// save_calibration.php - Writes calibration settings and mathematical basis to a local text file

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        $timestamp = date('Y-m-d H:i:s');
        $up = isset($data['threshUp']) ? floatval($data['threshUp']) : 0;
        $down = isset($data['threshDown']) ? floatval($data['threshDown']) : 0;
        $avgPeak = isset($data['avgPeakHigh']) ? floatval($data['avgPeakHigh']) : 0;
        $avgTrough = isset($data['avgTroughLow']) ? floatval($data['avgTroughLow']) : 0;
        $samples = isset($data['samplesCount']) ? intval($data['samplesCount']) : 0;
        
        $logContent = "==================================================\n";
        $logContent .= "FLEXIQ CALIBRATION RECORD - $timestamp\n";
        $logContent .= "==================================================\n";
        $logContent .= "Mathematical Basis & Calibration Settings:\n\n";
        $logContent .= "1. Recording Summary:\n";
        $logContent .= "   - Total Samples Collected: $samples cycles\n";
        $logContent .= "   - Average Bicep Peak Contraction (G): " . number_format($avgPeak, 3) . " G\n";
        $logContent .= "   - Average Bicep Release Trough (G):   " . number_format($avgTrough, 3) . " G\n\n";
        $logContent .= "2. Safety Multipliers Applied:\n";
        $logContent .= "   - Safety Factor: 70% of Peak / Trough\n\n";
        $logContent .= "3. Configured Threshold Settings:\n";
        $logContent .= "   - Proposed Up Threshold:   " . number_format($up, 1) . "\n";
        $logContent .= "   - Proposed Down Threshold: " . number_format($down, 1) . "\n";
        $logContent .= "   - Status: Applied & Synced in LocalStorage\n";
        $logContent .= "==================================================\n\n";
        
        // Write to calibration_basis.txt (appended history)
        file_put_contents('calibration_basis.txt', $logContent, FILE_APPEND);
        
        echo json_encode(["status" => "success", "message" => "Calibration basis appended to calibration_basis.txt"]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "Invalid request method or missing data"]);
?>
