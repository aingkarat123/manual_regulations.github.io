<?php

$policyChapters = [
    1 => [
        'title' => 'บททั่วไป',
        'summary' => 'หลักการ นิยาม และขอบเขตการใช้ข้อบังคับ',
        'keywords' => 'บททั่วไป หลักการ นิยาม ขอบเขต ข้อบังคับ',
        'title_en' => 'General Provisions',
        'summary_en' => 'Principles, definitions and scope of the regulations',
        'keywords_en' => 'general principles definitions scope regulations',
        'icon' => 'fa-book-open',
        'tone' => 'blue',
        'start' => 4,
        'end' => 6,
    ],
    2 => [
        'title' => 'การทำงานและการหยุดงาน',
        'summary' => 'วันทำงาน เวลาทำงาน เวลาพัก และวันหยุด',
        'keywords' => 'การทำงาน หยุดงาน วันทำงาน เวลาทำงาน เวลาพัก วันหยุด',
        'title_en' => 'Working Time and Days Off',
        'summary_en' => 'Workdays, working hours, rest periods and holidays',
        'keywords_en' => 'work working hours rest period holiday days off',
        'icon' => 'fa-calendar-days',
        'tone' => 'cyan',
        'start' => 7,
        'end' => 9,
    ],
    3 => [
        'title' => 'การบริหารค่าจ้าง ค่าล่วงเวลา เบี้ยเลี้ยง และค่าใช้จ่ายอื่นๆ',
        'summary' => 'ค่าจ้าง การปรับค่าจ้าง OT เบี้ยเลี้ยง และค่าใช้จ่าย',
        'keywords' => 'ค่าจ้าง เงินเดือน ค่าล่วงเวลา OT เบี้ยเลี้ยง ค่าใช้จ่าย',
        'title_en' => 'Wages, Overtime and Expenses',
        'summary_en' => 'Wages, overtime, allowances and other expenses',
        'keywords_en' => 'wage salary overtime OT allowance expenses',
        'icon' => 'fa-coins',
        'tone' => 'green',
        'start' => 10,
        'end' => 12,
    ],
    4 => [
        'title' => 'วันลาและหลักเกณฑ์การลา',
        'summary' => 'ประเภทการลา สิทธิวันลา และขั้นตอนการลา',
        'keywords' => 'วันลา การลา ลาป่วย ลากิจ ลาคลอด พักผ่อน',
        'title_en' => 'Leave and Leave Entitlements',
        'summary_en' => 'Types of leave, entitlements and request procedures',
        'keywords_en' => 'leave sick personal maternity annual vacation',
        'icon' => 'fa-person-walking-arrow-right',
        'tone' => 'orange',
        'start' => 13,
        'end' => 18,
    ],
    5 => [
        'title' => 'วินัยพนักงานและบทกำหนดโทษทางวินัย',
        'summary' => 'ข้อปฏิบัติ ความผิดทางวินัย และบทลงโทษ',
        'keywords' => 'วินัย พนักงาน บทลงโทษ ความผิด ลงโทษ',
        'title_en' => 'Discipline and Disciplinary Actions',
        'summary_en' => 'Employee conduct, violations and disciplinary actions',
        'keywords_en' => 'discipline employee conduct violation penalty',
        'icon' => 'fa-scale-balanced',
        'tone' => 'purple',
        'start' => 19,
        'end' => 22,
    ],
    6 => [
        'title' => 'ข้อพิพาท และการร้องทุกข์',
        'summary' => 'หลักเกณฑ์และขั้นตอนการยื่นเรื่องร้องทุกข์',
        'keywords' => 'ข้อพิพาท ร้องทุกข์ ร้องเรียน ขั้นตอน ยื่นเรื่อง',
        'title_en' => 'Disputes and Grievances',
        'summary_en' => 'Rules and procedures for submitting a grievance',
        'keywords_en' => 'dispute grievance complaint procedure',
        'icon' => 'fa-comments',
        'tone' => 'teal',
        'start' => 23,
        'end' => 23,
    ],
    7 => [
        'title' => 'การพ้นสภาพจากการเป็นพนักงาน',
        'summary' => 'การลาออก เลิกจ้าง และเหตุสิ้นสุดการเป็นพนักงาน',
        'keywords' => 'พ้นสภาพ ลาออก เลิกจ้าง เกษียณ พนักงาน',
        'title_en' => 'Termination of Employment',
        'summary_en' => 'Resignation, termination and retirement',
        'keywords_en' => 'resignation termination retirement employment',
        'icon' => 'fa-arrow-right-from-bracket',
        'tone' => 'red',
        'start' => 24,
        'end' => 25,
    ],
    8 => [
        'title' => 'เบ็ดเตล็ด',
        'summary' => 'ข้อกำหนดเพิ่มเติมและการมีผลบังคับใช้',
        'keywords' => 'เบ็ดเตล็ด ข้อกำหนดเพิ่มเติม มีผลบังคับใช้ ประกาศ',
        'title_en' => 'Miscellaneous',
        'summary_en' => 'Additional provisions and effective date',
        'keywords_en' => 'miscellaneous additional provisions effective date',
        'icon' => 'fa-file-lines',
        'tone' => 'slate',
        'start' => 26,
        'end' => 27,
    ],
];

function policyPageCount(array $chapter): int
{
    return ($chapter['end'] - $chapter['start']) + 1;
}

function policyPageLabel(array $chapter): string
{
    return $chapter['start'] === $chapter['end']
        ? 'หน้า ' . $chapter['start']
        : 'หน้า ' . $chapter['start'] . '-' . $chapter['end'];
}

