<?php
// School days Sunday through Thursday; dates are calculated for the current week.
$dayDefinitions = [
    ['day' => 'Sunday', 'color' => 'indigo'],
    ['day' => 'Monday', 'color' => 'violet'],
    ['day' => 'Tuesday', 'color' => 'fuchsia'],
    ['day' => 'Wednesday', 'color' => 'amber'],
    ['day' => 'Thursday', 'color' => 'teal'],
];
$today = new DateTimeImmutable('today');
$weekStart = $today->modify('-' . $today->format('w') . ' days');
$days = [];
foreach ($dayDefinitions as $offset => $definition) {
    $date = $weekStart->modify("+{$offset} days");
    $days[] = ['day' => $definition['day'], 'date' => $date->format('M j, Y'), 'color' => $definition['color']];
}
