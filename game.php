<?php

class Game
{
    private $word;
    private $hiddenWord;
    private $triedLetters;
    private $maxAttempts;
    private $attempts;

    public function __construct($maxAttempts, $file)
    {
        if ($maxAttempts < 1) {
            throw new Exception(sprintf('The attempts "%s" must be a valid integer greater than or equal than 1.', $maxAttempts));
        }

        $this->word = $this->getRandomWordFromFile($file);
        $this->hiddenWord = array_fill(0, mb_strlen($this->word), '_');
        $this->maxAttempts = (int) $maxAttempts;
        $this->attempts = 0;
        $this->triedLetters = array();
    }

    public function getRandomWordFromFile($filename)
    {
        $word = '';
        if (file_exists($filename)) {
            $fp = fopen($filename, 'r');
            $words = fgetcsv($fp, 100,';');
            $word = strtolower($words[array_rand($words)]);  
        } else {
            throw new Exception(sprintf('File not found in path : %s', $filename));
        }
        if ('' === $word) {
            throw new Exception('No word found');
        }
        return $word;
    }

    public function validateLetter($letter)
    {
        $ascii = ord(mb_strtolower($letter));
        if ($ascii < 97 || $ascii > 122) {
            throw new Exception('The expected letter must be a single character between A and Z.');
        }
        return $letter;
    }

    public function getWord()
    {
        return $this->word;
    }

    public function getHiddenWord()
    {
        return $this->hiddenWord;
    }

    public function getAttempts()
    {
        return $this->attempts;
    }

    public function tryWord($word)
    {
        $word = mb_strtolower($word);
        if ($word !== $this->word) {
            $this->attempts = $this->maxAttempts;
        } else {
            $this->hiddenWord = $this->getWordLetters();
            $this->attempts++;
        }
    }

    public function tryLetter($letter)
    {
        $this->validateLetter($letter);
        if (in_array($letter, $this->getWordLetters())) {
            $this->unmaskLetter($letter);
        }
        $this->triedLetters[] = $letter;
        $this->attempts++;
    }

    public function isOver()
    {
        return $this->isHanged() || $this->isWon();
    }

    public function isHanged()
    {
        return $this->attempts === $this->maxAttempts;
    }

    public function isWon()
    {
        return 0 === count(array_diff($this->getWordLetters(), $this->hiddenWord));
    }

    private function getWordLetters()
    {
        $letters = array();
        $length  = mb_strlen($this->word);
        for ($i = 0; $i < $length; $i++) {
            $letters[] = $this->word[$i];
        }
        return $letters;
    }

    private function unmaskLetter($letter)
    {
        foreach ($this->getWordLetters() as $k => $l) {
            if ($l === $letter) {
                $this->hiddenWord[$k] = $letter;
            }
        }
    }

    public function play()
    {
    	echo 'Welcome in the Hangman Game' . PHP_EOL;
        echo sprintf('You have %u attempts to guess the hidden word.', $this->attempts) . PHP_EOL;
        echo implode(' ', $this->getHiddenWord()) . PHP_EOL;
        do {
        	echo 'Type a letter... ' . PHP_EOL;
            if ($letter = trim(fgets(STDIN))) {
                $this->tryLetter($letter);
                echo implode(' ', $this->getHiddenWord()) . PHP_EOL;
            }
            if (!$letter) {
            	echo 'Try a word... ' . PHP_EOL;
            	$word = trim(fgets(STDIN));
                $this->tryWord($word);
            }
        } while (!$this->isOver());

        if (!$this->isWon()) {
            echo 'Perdu ! Le mot était : ' . $this->getWord() . PHP_EOL;
        } else {
            echo 'Gagné ! vous avez trouvé le mot : ' . $this->getWord() . PHP_EOL;
        }
    }
}
