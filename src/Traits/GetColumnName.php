<?php

namespace Matemat\TypeGenerator\Traits;

// Need to be tested
trait GetColumnName
{
    public function getColumnName($str)
    {
        $openingBracketPos = strpos($str, '(');
        $closingBracketPos = strpos($str, ')');
        // If no opening bracket is found, return null
        if ($openingBracketPos === false) {
            return null;
        }

        // Extract the substring after the opening bracket and before closing bracket
        $substring = substr($str, $openingBracketPos + 1, $closingBracketPos - $openingBracketPos - 1);

        // Match either single or double quoted text
        if (preg_match("/['\"]\s*([^'\"]+)\s*['\"]/", $substring, $matches)) {
            return $matches[1]; // Return the text inside quotes
        }

        return null; // Default to null if no valid content is found
    }
}
