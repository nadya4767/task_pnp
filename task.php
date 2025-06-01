<?php

$example_persons_array = [
    "Иванов Иван Иванович",
    "Сидоров Алексей Петрович",
    "Тихонов Михаил Андреевич",
    "Шевченко Юрий Николаевич",
    "Васильев Артем Владимирович",
    "Новиков Дмитрий Евгеньевич",
    "Орлов Николай Сергеевич",
    "Смирнов Владимир Аркадьевич",
    "Кузьмин Андрей Ильич",
    "Федоров Максим Валерьевич",
    "Зайцев Роман Никитич",
    "Петрова Мария Сергеевна",
    "Кузнецова Анна Владимировна",
    "Алексеева Ольга Петровна",
    "Ким Татьяна Ивановна",
    "Морозова Екатерина Алексеевна",
    "Соколова Елена Викторовна",
    "Лебедева Светлана Павловна",
    "Кравчук Александр Викторович",
    "Климко Евгения Петровна"
];

// Функция: склеивание ФИО из частей
function getFullnameFromParts($surname, $name, $patronomyc) {
    return trim("$surname $name $patronomyc");
}

// Функция: разбиение ФИО на части
function getPartsFromFullname($fullname) {
    $parts = explode(" ", trim($fullname));
    return [
        'surname' => $parts[0] ?? '',
        'name' => $parts[1] ?? '',
        'patronomyc' => $parts[2] ?? ''
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
    if (mb_substr($parts['patronomyc'], -3) === 'вна') {
        $genderScore--;
    } elseif (mb_substr($parts['patronomyc'], -2) === 'ич') {
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
        return getGenderFromName($person) === 1;
    }));
    $female = count(array_filter($persons, function($person) {
        return getGenderFromName($person) === -1;
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
function getPerfectPartner($surname, $name, $patronomyc, $persons) {
    // Приведение ФИО к нормальному регистру
    $surname = mb_convert_case(mb_strtolower($surname), MB_CASE_TITLE, "UTF-8");
    $name = mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, "UTF-8");
    $patronomyc = mb_convert_case(mb_strtolower($patronomyc), MB_CASE_TITLE, "UTF-8");

    $userFullname = getFullnameFromParts($surname, $name, $patronomyc);
    $userGender = getGenderFromName($userFullname);

    if ($userGender === 0) {
        return "Невозможно определить пол для указанного ФИО.";
    }

    // Поиск пары противоположного пола
    do {
        $randomPerson = $persons[rand(0, count($persons) - 1)];
        $randomGender = getGenderFromName($randomPerson);
    } while ($randomGender === 0 || $randomGender === $userGender);

    // Сокращённые имена
    $shortUser = getShortName($userFullname);
    $shortMatch = getShortName($randomPerson);

    // Случайный процент совместимости
    $compatibility = round(mt_rand(5000, 10000) / 100, 2);

    return "$shortUser + $shortMatch =\n♡ Идеально на $compatibility% ♡";
}
// --- Вывод ---
echo "<h3>Определение пола:</h3>";
foreach ($example_persons_array as $person) {
    $gender = getGenderFromName($person);
    $genderStr = $gender === 1 ? 'мужской' : ($gender === -1 ? 'женский' : 'неопределённый');
    echo "<strong>ФИО:</strong> $person — Пол: $genderStr<br>";
}

echo "<h3>Гендерный состав аудитории:</h3><pre>";
echo getGenderDescription($example_persons_array);
echo "</pre>";

echo "<h3>Идеальный подбор пары:</h3><pre>";
echo getPerfectPartner("иванов", "иван", "иванович", $example_persons_array);
echo "</pre>";

?>