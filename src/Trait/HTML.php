<?php

namespace App\Trait;

trait HTML
{
    /**
     * Формирует из текста код
     *
     * @param string $text
     * @param string|null $ln
     * @return string
     */
    public function textToCodeHTML(string $text, string $ln = null): string
    {
        if ($ln){
            return "<pre><code class='language-{$ln}'>". $text .'</code></pre>';
        }
        return '<pre>'. $text .'</pre>';
    }

    /**
     * Формирует из текста код
     *
     * @param string $text
     * @param string|null $ln
     * @return string
     */
    public function textToBoldHTML(string $text): string
    {
        return '<b>'. $text .'</b>';
    }

    public function br(): string
    {
        return '<br>';
    }

}
