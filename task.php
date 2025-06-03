<?php

$example_persons_array = [
    ['fullname' => 'Иванов Иван Иванович', 'job' => 'tester'],
    ['fullname' => 'Степанова Наталья Степановна', 'job' => 'frontend-developer'],
    ['fullname' => 'Пащенко Владимир Александрович', 'job' => 'analyst'],
    ['fullname' => 'Громов Александр Иванович', 'job' => 'fullstack-developer'],
    ['fullname' => 'Славин Семён Сергеевич', 'job' => 'analyst'],
    ['fullname' => 'Цой Владимир Антонович', 'job' => 'frontend-developer'],
    ['fullname' => 'Быстрая Юлия Сергеевна', 'job' => 'PR-manager'],
    ['fullname' => 'Шматко Антонина Сергеевна', 'job' => 'HR-manager'],
    ['fullname' => 'аль-Хорезми Мухаммад ибн-Муса', 'job' => 'analyst'],
    ['fullname' => 'Бардо Жаклин Фёдоровна', 'job' => 'android-developer'],
    ['fullname' => 'Шварцнегер Арнольд Густавович', 'job' => 'babysitter'],
];

// Функция: склеивание ФИО из частей
function getFullnameFromParts($surname, $name, $patronymic) {
    return trim("$surname $name $patronymic");
}

// Функция: разбиение ФИО на части
function getPartsFromFullname($fullname) {
    $parts = explode(" ", trim($fullname));
    return [
        'surname' => $parts[0] ?? '',
        'name' => $parts[1] ?? '',
        'patronymic' => $parts[2] ?? ''
    ];
}

// Функция: сокращение ФИО
function getShortName($fullname) {
    $parts = getPartsFromFullname($fullname);
    $name = $parts['name'];
    $surnameInitial = mb_substr($parts['surname'], 0, 1);
    return "$name $surnameInitial.";
}

// Функция: определение пола
function getGenderFromName($fullname) {
    $parts = getPartsFromFullname($fullname);
    $genderScore = 0;

    // Отчество
    if (mb_substr($parts['patronymic'], -3) === 'вна') {
        $genderScore--;
    } elseif (mb_substr($parts['patronymic'], -2) === 'ич') {
        $genderScore++;
    }

    // Имя
    if (mb_substr($parts['name'], -1) === 'а') {
        $genderScore--;
    } elseif (in_array(mb_substr($parts['name'], -1), ['й', 'н'])) {
        $genderScore++;
    }

    // Фамилия
    if (mb_substr($parts['surname'], -2) === 'ва') {
        $genderScore--;
    } elseif (mb_substr($parts['surname'], -1) === 'в') {
        $genderScore++;
    }

    return $genderScore > 0 ? 1 : ($genderScore < 0 ? -1 : 0);
}

// Описание гендерного состава
function getGenderDescription($persons) {
    $total = count($persons);
    $male = count(array_filter($persons, function($person) {
        return getGenderFromName($person['fullname']) === 1;
    }));
    $female = count(array_filter($persons, function($person) {
        return getGenderFromName($person['fullname']) === -1;
    }));
    $undefined = $total - $male - $female;

    $malePercent = round($male / $total * 100, 1);
    $femalePercent = round($female / $total * 100, 1);
    $undefinedPercent = round($undefined / $total * 100, 1);

    return "Гендерный состав аудитории:\n---------------------------\n" .
           "Мужчины - {$malePercent}%\n" .
           "Женщины - {$femalePercent}%\n" .
           "Не удалось определить - {$undefinedPercent}%";
}

// 🔸 Новая функция: идеальный подбор пары
function getPerfectPartner($surname, $name, $patronymic, $persons) {
    // Приведение ФИО к нормальному регистру
    $surname = mb_convert_case(mb_strtolower($surname), MB_CASE_TITLE, "UTF-8");
    $name = mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, "UTF-8");
    $patronymic = mb_convert_case(mb_strtolower($patronymic), MB_CASE_TITLE, "UTF-8");

    $userFullname = getFullnameFromParts($surname, $name, $patronymic);
    $userGender = getGenderFromName($userFullname);

    if ($userGender === 0) {
        return "Невозможно определить пол для указанного ФИО.";
    }

    // Поиск пары противоположного пола
    $oppositeGenderPersons = array_filter($persons, function ($person) use ($userGender) {
        return getGenderFromName($person['fullname']) === -$userGender;
    });

    if (empty($oppositeGenderPersons)) {
        return "Не найдено подходящих партнёров противоположного пола.";
    }

    $randomPerson = $oppositeGenderPersons[array_rand($oppositeGenderPersons)];

    // Сокращённые имена
    $shortUser = getShortName($userFullname);
    $shortMatch = getShortName($randomPerson['fullname']);

    // Случайный процент совместимости
    $compatibility = round(mt_rand(5000, 10000) / 100, 2);

    return "$shortUser + $shortMatch =\n♡ Идеально на $compatibility% ♡";
}
// --- Вывод ---
echo "<h3>Определение пола:</h3><br>";
foreach ($example_persons_array as $person) {
    $gender = getGenderFromName($person['fullname']);
    $genderStr = $gender === 1 ? 'мужской' : ($gender === -1 ? 'женский' : 'неопределённый');
    echo "<strong>ФИО:</strong> {$person['fullname']} — Пол: $genderStr<br>";
}

echo "<h3>Гендерный состав аудитории:</h3><pre>";
echo getGenderDescription($example_persons_array);
echo "</pre>";

echo "<h3>Идеальный подбор пары:</h3><pre>";
echo getPerfectPartner("иванов", "иван", "иванович", $example_persons_array);
echo "</pre>";

?>