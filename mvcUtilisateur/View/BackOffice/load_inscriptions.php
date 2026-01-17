<?php
require_once(__DIR__ . '../../../Controller/UserController.php');
header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : '1 MONTH';
$controller = new userController();
$dataInscriptions = $controller->getInscriptionTrends($period);

$labelsInscriptions = [];
$totalsInscriptions = [];

if ($period === '7 DAY') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }
    for ($i = 6; $i >= 0; $i--) {
        $date = date('d M', strtotime("-$i day"));
        $labelsInscriptions[] = $date;
        $totalsInscriptions[] = isset($map[$date]) ? $map[$date] : 0;
    }

} elseif ($period === '1 MONTH') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }

    // Générer les 4 dernières semaines glissantes (aujourd'hui - 7, -14, -21, -28 jours)
    $labelsInscriptions = [];
    $totalsInscriptions = [];

    for ($i = 3; $i >= 0; $i--) {
        $date = strtotime("-" . ($i * 7) . " days");
        $weekNumber = date('W', $date);
        $year = date('Y', $date);
        $label = "Semaine $weekNumber/$year"; // ex: Semaine 15/2025
        $labelsInscriptions[] = $label;
        $totalsInscriptions[] = isset($map[$label]) ? $map[$label] : 0;
    }


} elseif ($period === '4 MONTH') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }
    for ($i = 3; $i >= 0; $i--) {
        $label = date('M Y', strtotime("-$i month"));
        $labelsInscriptions[] = $label;
        $totalsInscriptions[] = isset($map[$label]) ? $map[$label] : 0;
    }

} elseif ($period === '6 MONTH') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }
    for ($i = 5; $i >= 0; $i--) {
        $label = date('M Y', strtotime("-$i month"));
        $labelsInscriptions[] = $label;
        $totalsInscriptions[] = isset($map[$label]) ? $map[$label] : 0;
    }

} elseif ($period === '1 YEAR') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }

    $currentQuarter = ceil(date('n') / 3);
    $currentYear = date('Y');
    for ($i = 3; $i >= 0; $i--) {
        $q = $currentQuarter - $i;
        $year = $currentYear;
        if ($q <= 0) {
            $q += 4;
            $year -= 1;
        }
        $label = "T$q-$year";
        $labelsInscriptions[] = $label;
        $totalsInscriptions[] = isset($map[$label]) ? $map[$label] : 0;
    }

} elseif ($period === '3 YEAR') {
    $map = [];
    foreach ($dataInscriptions as $row) {
        $map[$row['label']] = (int)$row['total'];
    }

    $currentYear = date('Y');
    for ($i = 2; $i >= 0; $i--) {
        $year = $currentYear - $i;
        $labelsInscriptions[] = (string)$year;
        $totalsInscriptions[] = isset($map[$year]) ? $map[$year] : 0;
    }

} else {
    foreach ($dataInscriptions as $row) {
        $labelsInscriptions[] = $row['label'];
        $totalsInscriptions[] = (int)$row['total'];
    }
}

// 🔍 Pour debug si besoin
// file_put_contents("debug_inscription.log", print_r(['period' => $period, 'result' => $dataInscriptions], true));

echo json_encode([
    'labels' => $labelsInscriptions,
    'totals' => $totalsInscriptions
]);
