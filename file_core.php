<?php

class FileStreamMode {
    public const read = "r";
    public const append = "a";
    public const write = "w";
    public const readWrite = "w+";
    public const readAppend = "a+";
    public const readPrepend = "r+";
}

class FileStream {
    protected $fileStream;
    protected $_canWrite = false;
    protected $_canRead = false;

    protected function interpretMode($mode) {
        switch($mode) {
            case "r":
                $this->_canRead = true;
            break;
            case "w":
            case "a":
                $this->_canWrite = true;
            break;
            case "r+":
            case "w+":
            case "a+":
                $this->_canWrite = true;
                $this->_canRead = true;
            break;
            default:
                return false;
        }
        return true;
    }

    function __construct($filePath, $mode) {
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        if ($this->interpretMode($mode)) {
            $this->fileStream = fopen($filePath, $mode);
        } else {
            throw("Error: Invalid File Stream Mode String. (" + $mode + ")");
        }
    } 

    function __destruct() {
        fclose($this->fileStream);
    }

    function canWrite() {
        if (!$this->_canWrite) {
            $this->writeError();
        }
        return $this->_canWrite;
    }
    
    function writeError() {
        throw("You can't write to file while not using correct File Stream mode.");
    }

    function write($string) {
        if($this->canWrite()) {
            fwrite($this->fileStream, $string);
        }
    }

    function writeLine($string) {
        $this->write($string . "\nx");
    }

    protected function canRead() {
        if (!$this->_canRead) {
            readError();
        }
        return $this->_canRead;
    }
    
    function readError() {
        throw("You can't read to file while not using correct File Stream mode.");
    }

    function resetPointer() {
        rewind($this->fileStream);
    }

    function read($lenght) {
        if($this->canRead()) {
            return fread($this->fileStream, $lenght);
        }
        return "";
    }

    function readLine($addBreakLine = true) {
        if($this->canRead()) {
            if ($addBreakLine) return fgets($this->fileStream) . "<br>";
            else return fgets($this->fileStream);
        }
        return "";
    }

    function readLines($amount, $addBreakLine = true) {
        if($this->canRead()) {
            $return = "";
            for($i = 0; $i < $amount; $i++) {
                $return .= $this->readLine($addBreakLine);
            }
            return $return;
        }
        return "";
    }

    function readFile($addBreakLine = true) {
        $this->resetPointer();
        if ($this->canRead()) {
            $fileContent = "";
            while(!feof($this->fileStream)) {
                $fileContent .= $this->readLine($addBreakLine);
            }
            return $fileContent;
        }
        return "";
    }

    function getPointer() {
        if(!$this->canRead()) return;
        return ftell($this->fileStream);
    }

    function setPointer($index) {
        if(!$this->canRead()) return;
        fseek($this->fileStream, $index);
    }
    
    function setPointerToLine($line) {
        if(!$this->canRead()) return;
        $this->resetPointer();
        $this->readLines($line);
    }

    function isOnFileEnd() {
        if (!$this->canRead()) return true;
        return feof($this->fileStream);
    }
}

class File {
    protected $filePath;
    protected $pointer = 0;

    protected function storePointer($fileStream) {
        $this->pointer = $fileStream->getPointer();
    }

    protected function loadPointer($fileStream) {
        $fileStream->setPointer($this->pointer);
    }

    function __construct($filePath) {
        $this->filePath = $filePath;
        if (!file_exists($filePath)) {
            touch($filePath);
        }
    }

    function overwrite($string = "") {
        $writeStream = new FileStream($this->filePath, FileStreamMode::write);
        $writeStream->write($string);
    }

    function overwriteLine($string = "") {
        $this->overwrite($string . "\n");
    }

    function prepend($string = "") {
        $this->overwrite($string . $this->readFile(false));

        //$prependStream = new FileStream($this->filePath, FileStreamMode::readPrepend);
        //$string = $string . $this->readFirst();
        //$prependStream->write($string);
    }
    
    function prependLine($string = "") {
        $this->prepend($string . "\n");
    }

    function append($string = "") {
        $appendStream = new FileStream($this->filePath, FileStreamMode::append);
        $appendStream->write($string);
    }

    function appendLine($string = "") {
        $this->append($string . "\n");
    }

    function read($amount = 1) {
        $readStream = new FileStream($this->filePath, FileStreamMode::read);
        $this->loadPointer($readStream);
        $return = $readStream->read($amount);
        $this->storePointer($readStream);
        return $return;
    }

    # -1 for next line
    function readLine($index = -1, $addBreakLine = true) {
        return $this->readLines(1, $index, $addBreakLine);
    }

    # -1 for next line
    function readLines($amount = 1, $index = -1, $addBreakLine = true) {
        $readStream = new FileStream($this->filePath, FileStreamMode::read);
        if ($index < 0) {
            $this->loadPointer($readStream);
        } else {
            $readStream->setPointerToLine($index);
        }
        $return = $readStream->readLine($amount, $addBreakLine);
        $this->storePointer($readStream);
        return $return;
    }

    function readFile($addBreakLine = true) {
        $readStream = new FileStream($this->filePath, FileStreamMode::read);
        return $readStream->readFile($addBreakLine);
    }

    function isOnFileEnd() {
        // BUG: always returns false
        $readStream = new FileStream($this->filePath, FileStreamMode::read);
        $this->loadPointer($readStream);
        return $readStream->isOnFileEnd();
    }
}

?>