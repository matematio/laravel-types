<?php

namespace Matemat\TypeGenerator\Traits;

trait CommentRemover
{
    public function removeComment($content): string
    {
        // Regular expression to match PHP comments
        $pattern = [
            '/\/\*[\s\S]*?\*\//',  // Matches block comments: /* */
            '/\/\/.*$/m',          // Matches single-line comments: //
        ];

        // Remove comments from the content
        $contentWithoutComments = preg_replace($pattern, '', $content);

        return $contentWithoutComments;
    }
}
