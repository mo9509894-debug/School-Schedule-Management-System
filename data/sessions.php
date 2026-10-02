<?php
// =====================================================================
// ملف الحصص (الدروس). ده الملف اللي أصحابك هيشتغلوا عليه مع الداتابيز.
//
// الشكل: $sessions[الصف][الفصل][اليوم] = قائمة حصص
// يعني: اختار Grade 10 ← Class A ← Sunday، الموقع بيفتح:
//       $sessions['10']['A']['Sunday']
//
// كل حصة بتتكتب بالدالة lesson() بالترتيب ده:
//   lesson(اسم المادة, وقت البداية, وقت النهاية, النوع, شرح اللي هيتدرّس)
//   النوع: 'class' للحصة، 'break' للراحة (والشرح بيبقى فاضي)
//
// الباك إند: استبدلوا الملف ده بـ SELECT من جدول الحصص، وخلّوا
// المصفوفة بنفس الشكل: $sessions[$grade][$class][$day][] = [id, name, start, end, type, desc]
// =====================================================================

$sessions = [];
$next_id = 1; // رقم تعريف لكل حصة (في الداتابيز هيبقى الـ id)

// دالة بتكوّن الحصة الواحدة
function lesson($name, $start, $end, $type, $desc, $teacher = null) {
    global $next_id;
    $teacherMap = [
        'Mathematics' => 'Mr. Ahmed Hassan', 'English' => 'Ms. Sara Ali',
        'Science' => 'Dr. Mona Samir', 'History' => 'Mr. Karim Adel',
        'Physical Education' => 'Coach Omar Nabil', 'Arabic' => 'Ms. Hala Mahmoud',
        'Computer Science' => 'Mr. Youssef Tarek', 'Physics' => 'Dr. Amr Khaled',
        'Chemistry' => 'Ms. Nada Ibrahim', 'Programming' => 'Mr. Mostafa Fathy',
        'Networking' => 'Mr. Ziad Ashraf', 'Electronics' => 'Eng. Mariam Sameh',
    ];
    if ($teacher === null) $teacher = ($type === 'break') ? '' : ($teacherMap[$name] ?? 'Mr. Ahmed Hassan');
    return ['id' => $next_id++, 'name' => $name, 'start' => $start, 'end' => $end, 'type' => $type, 'desc' => $desc, 'teacher' => $teacher];
}

// ---------------------------------------------------------------------
// مثال مكتوب بالإيد: Grade 10 - Class A (كل أيام الأسبوع)
// ---------------------------------------------------------------------
$sessions['10']['A']['Sunday'] = [
    lesson('Mathematics',        '8:00 AM',  '9:00 AM',  'class', 'Today: Quadratic equations.'),
    lesson('English',            '9:00 AM',  '10:00 AM', 'class', 'Today: Reading comprehension.'),
    lesson('Break',              '10:00 AM', '10:30 AM', 'break', ''),
    lesson('Science',            '10:30 AM', '11:30 AM', 'class', 'Today: Electric circuits.'),
    lesson('History',            '11:30 AM', '12:30 PM', 'class', 'Today: The modern Egyptian state.'),
    lesson('Break',        '12:30 PM', '1:00 PM',  'break', ''),
    lesson('Physical Education', '1:00 PM',  '2:00 PM',  'class', 'Today: Warm-up routine and basketball passing.'),
];
$sessions['10']['A']['Monday'] = [
    lesson('Arabic',             '8:00 AM',  '9:00 AM',  'class', 'Today: Grammar and syntax.'),
    lesson('Computer Science',   '9:00 AM',  '10:00 AM', 'class', 'Today: Intro to algorithms.'),
    lesson('Break',              '10:00 AM', '10:30 AM', 'break', ''),
    lesson('Mathematics',        '10:30 AM', '11:30 AM', 'class', 'Today: Functions and graphs.'),
    lesson('Physics',            '11:30 AM', '12:30 PM', 'class', 'Today: Waves and sound.'),
    lesson('Break',        '12:30 PM', '1:00 PM',  'break', ''),
    lesson('English',            '1:00 PM',  '2:00 PM',  'class', 'Today: Writing a formal email.'),
];
$sessions['10']['A']['Tuesday'] = [
    lesson('Chemistry',          '8:00 AM',  '9:00 AM',  'class', 'Today: The periodic table.'),
    lesson('Programming',        '9:00 AM',  '10:00 AM', 'class', 'Today: Variables and loops.'),
    lesson('Break',              '10:00 AM', '10:30 AM', 'break', ''),
    lesson('History',            '10:30 AM', '11:30 AM', 'class', 'Today: Ancient Egyptian civilization.'),
    lesson('Mathematics',        '11:30 AM', '12:30 PM', 'class', 'Today: Trigonometry basics.'),
    lesson('Break',        '12:30 PM', '1:00 PM',  'break', ''),
    lesson('Arabic',             '1:00 PM',  '2:00 PM',  'class', 'Today: Poetry analysis.'),
];
$sessions['10']['A']['Wednesday'] = [
    lesson('Networking',         '8:00 AM',  '9:00 AM',  'class', 'Today: IP addressing.'),
    lesson('Science',            '9:00 AM',  '10:00 AM', 'class', 'Today: Chemical reactions.'),
    lesson('Break',              '10:00 AM', '10:30 AM', 'break', ''),
    lesson('English',            '10:30 AM', '11:30 AM', 'class', 'Today: Grammar: tenses.'),
    lesson('Electronics',        '11:30 AM', '12:30 PM', 'class', 'Today: Resistors and capacitors.'),
    lesson('Break',        '12:30 PM', '1:00 PM',  'break', ''),
    lesson('Physical Education', '1:00 PM',  '2:00 PM',  'class', 'Today: Fitness circuit.'),
];
$sessions['10']['A']['Thursday'] = [
    lesson('Programming',        '8:00 AM',  '9:00 AM',  'class', 'Today: Functions in PHP.'),
    lesson('Mathematics',        '9:00 AM',  '10:00 AM', 'class', 'Today: Revision and practice.'),
    lesson('Break',              '10:00 AM', '10:30 AM', 'break', ''),
    lesson('Physics',            '10:30 AM', '11:30 AM', 'class', 'Today: Ohm\'s law.'),
    lesson('Arabic',             '11:30 AM', '12:30 PM', 'class', 'Today: Essay writing.'),
    lesson('Break',        '12:30 PM', '1:00 PM',  'break', ''),
    lesson('Computer Science',   '1:00 PM',  '2:00 PM',  'class', 'Today: HTML and CSS basics.'),
];

// ---------------------------------------------------------------------
// باقي الصفوف والفصول: بيانات تجريبية بتتولّد أوتوماتيك (كل فصل ويوم مختلف)
// علشان تشوفوا الموقع شغال بالكامل. امسحوا الجزء ده لما الداتابيز تجهز.
// ---------------------------------------------------------------------
$topics = [
    'Mathematics' => 'Problem solving',   'English' => 'Speaking and writing',
    'Science'     => 'Lab experiment',    'History' => 'Key events and dates',
    'Arabic'      => 'Reading and grammar', 'Physics' => 'Practical examples',
    'Chemistry'   => 'Reactions and safety', 'Computer Science' => 'Hands-on practice',
    'Networking'  => 'Network tools',     'Electronics' => 'Circuit building',
    'Programming' => 'Writing code',      'Physical Education' => 'Fitness drills',
];
$names = array_keys($topics);
$slots = [
    ['8:00 AM', '9:00 AM', 'class'],   ['9:00 AM', '10:00 AM', 'class'],  ['10:00 AM', '10:30 AM', 'break'],
    ['10:30 AM', '11:30 AM', 'class'], ['11:30 AM', '12:30 PM', 'class'], ['12:30 PM', '1:00 PM', 'break'],
    ['1:00 PM', '2:00 PM', 'class'],
];
foreach ($grades as $gi => $g) {
    foreach ($classes as $ci => $c) {
        foreach ($days as $di => $d) {
            if (isset($sessions[$g['grade']][$c['class']][$d['day']])) continue; // المكتوب بالإيد يفضل زي ما هو
            $list = [];
            foreach ($slots as $si => $slot) {
                if ($slot[2] === 'break') {
                    $list[] = lesson($si === 2 ? 'Break' : 'Break', $slot[0], $slot[1], 'break', '');
                } else {
                    $name = $names[($gi * 3 + $ci * 2 + $di * 3 + $si) % count($names)]; // المادة بتتغير حسب الصف والفصل واليوم
                    $list[] = lesson($name, $slot[0], $slot[1], 'class', 'Today: ' . $topics[$name] . '.');
                }
            }
            $sessions[$g['grade']][$c['class']][$d['day']] = $list;
        }
    }
}
