<?php
/**
 * Returns true if $needle is a substring of $haystack.
 */
function contains(string $needle, string $haystack): bool
{
    return mb_strpos($haystack, $needle) !== false;
}

/**
 * Look for scarry input and return true if found.
 */
function isScarry(string $haystack): bool
{
    return preg_match('/[^a-z0-9_-]/ui', $haystack);
}

if (!isset($_POST['repo'], $_POST['fileName'], $_POST['fileContent'], $_POST['functionName'])) {
    die('No input!');
}

if (!ctype_digit($_POST['repo'])
    || !preg_match('/^[a-z0-9_-]+\.(cpp|h)$/ui', $_POST['fileName'])
    || isScarry($_POST['functionName'])
) {
    die('Invalid input!');
}

$filePath = 'devilution_' . $_POST['repo'] . '/Source/' . $_POST['fileName'];

$myfile = fopen($filePath, 'w');
if (!$myfile) {
    die('Unable to open file!');
}

fwrite($myfile, $_POST['fileContent']);
fclose($myfile);
$out      = '';
$mvse     = [];
$hideaddr = '';
if (array_key_exists('hideaddr', $_POST)) {
    $hideaddr = '--no-mem-disp';
}

$out = exec('compare.bat ' . $_POST['repo'] . ' ' . $_POST['functionName'] . ' ' . $hideaddr, $mvse, $out);

$skipped = 0;
foreach ($mvse as $line) {
    if ($line === '') {
        continue;
    }

    if (!contains($_POST['fileName'], $line)
        && !contains('Found ' . $_POST['functionName'], $line)
        && !contains(' error ', $line)
        && !contains('Could not find the specified symbol in the config', $line)
    ) {
        continue;
    }

    if ($skipped < 2) {
        $skipped++;
        continue;
    }

    echo json_encode(htmlspecialchars($line));
    break;
}
