<?php
// copilot: disable
// @ts-nocheck
require_once "base.php";

$ucid = "kc784"; // <-- set your ucid

// Don't edit the arrays below, they are used to test your code
$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];


function transformText($arr, $arrayNumber) {
    echo "<div class='problem-item'>";
    // Only make edits between the designated "Start" and "End" comments
    printScenario4ArrayInfo($arr, $arrayNumber);
    // This should be solved without Copilot auto-completion, to toggle it, click the Copilot chat bubble at the top of the editor.
    //  Configure inline suggestions to "Disabled Inline Suggestions" (or similar) when writing code for this problem.
   
    // Challenge 1: Remove non-alphanumeric characters except spaces
    // Challenge 2: Convert text to Title Case
    // Challenge 3: Remove leading/trailing spaces and remove duplicate spaces between words
    // Result 1-3: Assign final phrase to `placeholderForModifiedPhrase`
    // Challenge 4 (extra credit): Extract up to middle 3 characters (beginning starts at middle of phrase, exclude the first and last character for shorter phrases, effectively middle index +/- 1)
    // Assign result to 'placeholderForMiddleCharacters'
    // If not enough characters in a word, instead assign "Not enough characters" to `placeholderForMiddleCharacters`

    $placeholderForModifiedPhrase = "";
    $placeholderForMiddleCharacters = "";
    foreach ($arr as $index => $text) {
        // Start Solution Edits
        // Step 1: sketch out plan using comments (include ucid and date)
        // Step 2: Add/commit your outline of comments (required for full credit)
        // Step 3: Add code to solve the problem (add/commit as needed)

        // kc784 | 2/23
        // build new string only keeping letters, nums, spaces (no punctuation) 
        // make vals positive
        // trim extra spaces from beginning and end
        // keep one space 
        // convert to title
        // len of modified phrase
        // if too short then run too short
        // 3 middle chars

        $cleaned = "";
        for ($i = 0; $i < strlen($text); $i++) {
            $ch = $text[$i];
            $isLower = ($ch >= "a" && $ch <= "z");  // checks for lowercase 
            $isUpper = ($ch >= "A" && $ch <= "Z"); // checks for uppercase
            $isNum = ($ch >= "0" && $ch <= "9"); // checks for num
            $isSpace = ($ch === " ");   //checks for space

            if ($isLower || $isUpper || $isNum || $isSpace) {
                $cleaned .= $ch;    // stores all non alphanum to cleaned
            }
        }
        $cleaned = trim ($cleaned); // holder for cleaning extra spaces from start and end
        $normal = "";
        $spaceBefore = false;   // boolean holder for checking spaces before element

        for ($i = 0; $i < strlen($cleaned); $i++) {
            $ch = $cleaned[$i];

            if ($ch === " ") {      // if current char is space
                if (!$spaceBefore) {
                    $normal .= $ch;     // adds a space
                    $spaceBefore = true;    // last added char is space
                }
            } else {
                $normal .= $ch;     // if not space then add it
                $spaceBefore = false;
            }
        }
        $placeholderForModifiedPhrase = ucwords (strtolower($normal));  // lowercase for everything
        $phrase = $placeholderForModifiedPhrase;   
        $len = strlen($phrase);     // length of modified phrase

        if ($len < 3) {     // checks if word is long enough
            $placeholderForMiddleCharacters = "Not enough characters";
        } else {
            $mid = intdiv($len, 2);     // middle index
            $start = $mid - 1;  // first letter of middle 3 characters

            if ($start < 1) {
                $start = 1;
            }

            if ($start + 2 >= $len) {   // tries to keep start index in middle of phrase instead of edges
                $start = $len - 3;
            }
            if ($start < 0) {
                $placeholderForMiddleCharacters = "Not enough characters";
            } else {
                $middlePart = substr($phrase, $start, 3);   // takes 3 chars
                if ($middlePart === "") {
                    $placeholderForMiddleCharacters = "Not enough characters";
                } else {
                    $placeholderForMiddleCharacters = $middlePart;  // saves middle characters result
                }
            }
        }
        // End Solution Edits
    
        printScenario4Transformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
        
    }

    echo "</div>";
}

// Run the problem
printHeader($ucid, 4);
echo "<div class='scenario4-grid'>";
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
echo "</div>";
printFooter($ucid, 4);

?>