<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class Message {
    private string $originalMessage;
    private string $messageReverted;

    public function __construct(string $message){
        $this->originalMessage = $message;
        $this->messageReverted = $this->revertCharacters($message);
    }

    /**
     * Аналог substr_replace c поддержкой UTF-8
     * @param string $original
     * @param string $replacement
     * @param int $position
     * @param int $length
     * @return string
     */
    public function mb_substr_replace(string $original, string $replacement, int $position, int $length): string
    {
        $startString = mb_substr($original, 0, $position, "UTF-8");
        $endString = mb_substr($original, $position + $length, mb_strlen($original), "UTF-8");

        return $startString . $replacement . $endString;
    }

    /**
     * Аналог strrev c поддержкой UTF-8 и вставкой пунктуации с позицией из оригинальной строки
     * @param string $str
     * @return string
     */
    public function mb_strrev(string $str): string
    {
        $r = '';
        $punct_pos = [];
        $upper_pos = [];
        for ($i = mb_strlen($str); $i>=0; $i--) {
            $char = mb_substr($str, $i, 1);

            if (!ctype_punct($char)) {
                if (mb_strtoupper($char) === $char && !ctype_punct($char) && $char !== '') {
                    // помещаем в массив индексы элементов С большой буквы
                    array_push($upper_pos, $i);
                }
                $r .= mb_strtolower($char);
            } else {
                // помещаем в массив индексы элементов пунктуации
                array_push($punct_pos, $i);
            }
        }

        // Подстановка знаков
        foreach ($punct_pos as $pos) {
            $r = $this->mb_substr_replace($r, mb_substr($str, $pos, 1), $pos+1, 0);
        }

        // Подстановка больших букв
        foreach ($upper_pos as $pos) {
            $upper = mb_substr($r, $pos, 1);
            $r = $this->mb_substr_replace($r, mb_strtoupper($upper), $pos, 0);
            $r = $this->mb_substr_replace($r, '', ++$pos, 1);
        }
        return $r;
    }

    /**
     * Принимает на вход строку и меняет порядок букв в каждом слове на обратный с сохранением регистра и пунктуации.
     * @param string $words
     * @return string
     */
    function revertCharacters(string $words): string
    {
        $words = preg_split('/\s+/', $words);
        $str = '';
        foreach ($words as  $key => $word) {
            $space = ($key !== 0) ? ' ': '';
            $str = $str . $space . $this->mb_strrev($word);
        }
        return $str;
    }

    public function printMsg()
    {
        print($this->messageReverted);
    }
}

class TestMessage extends TestCase
{
    public function testMessageIsReversed(): void
    {
        $expected = 'Тевирп! Онвад ен ьсиледив.';
        $this->expectOutputString($expected);
        $msg = new Message('Привет! Давно не виделись.');
        $msg->printMsg();
    }

    public function testMessageIsAllUppercase(): void
    {
        $expected = 'THAT TEST MESSAGE';
        $this->expectOutputString($expected);
        $msg = new Message('TAHT TSET EGASSEM');
        $msg->printMsg();
    }

    public function testMessageIsAllLowercase(): void
    {
        $expected = 'that test';
        $this->expectOutputString($expected);
        $msg = new Message('taht tset');
        $msg->printMsg();
    }

    public function testMessageWithSpecialChars(): void
    {
        $expected = 'Dr@0WHtp!';
        $this->expectOutputString($expected);
        $msg = new Message('P@thW0rd!');
        $msg->printMsg();
    }
}



