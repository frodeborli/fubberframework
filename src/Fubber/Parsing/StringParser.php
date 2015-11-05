<?php
namespace Fubber\Parsing;
/**
*       Usage:
*       $parser = new StringParser('Some "string to be" parsed', TRUE);         // TRUE means that you want to ignore whitespace
*
*       while($data = $parser->consume()) {
*               echo "Got: '$data'. Type is '".$parser->type."'\n";             // TYPE is one of the StringParser::CONSUMED*-constants or NULL
*       }
*
*       Peeking on the next token:
*       $peek = $parser->consume();
*       $parser->push($peek);                                                   // Adds $data to the beginning of the string and will be returned again
*
*       The parser will generally work on any type of string, and will split it by whitespace, quoted strings, single words, integers and floats. For example:
*       123.456.789 will be returned as first "123.456" of type FLOAT, then "." of type SYMBOLS then 789 of type INTEGER.
*/
class StringParser {
        const CONSUMED_WHITESPACE = 1;          // Whitespace was consumed
        const CONSUMED_QUOTED = 2;              // A quoted string was consumed, either single quote or double quotes
        const CONSUMED_WORD = 3;                // A word, consisting of ASCII and UTF-8 characters until ascii non-characters was found
        const CONSUMED_INTEGER = 4;             // A number, either integer or float (0, 131, 0.1234)
        const CONSUMED_FLOAT = 5;               // A number, either integer or float (0, 131, 0.1234)
        const CONSUMED_SYMBOLS = 6;             // Any ascii symbol, such as commas, parenthesis
	const CONSUMED_NOTHING = 0;		// There's nothing left to parse

        public $type = NULL;
        public $_rest;
        public $_l;
        protected $_skipWhitespace = FALSE;

        public function __construct($string, $skipWhitespace = FALSE) {
//              if(!$string) throw new Exception("Must have a string!");
                if(gettype($string)!=='string') throw new Exception("Must have a String!");
                $this->_rest = $string;
                $this->_l = mb_strlen($string, 'UTF-8');
                $this->_skipWhitespace = $skipWhitespace;
        }

        public function push($string) {
                $this->_rest = $string.($this->_skipWhitespace?' ':'').$this->_rest;
                $this->_l = mb_strlen($this->_rest, 'UTF-8');
                return $this;
        }

        /**
        *       Consume some data from the string and return it
        */
        public function consume() {
                if($this->_skipWhitespace) {
                        $this->_skipWhitespace = FALSE;
                        $res = $this->consume();
                        $this->_skipWhitespace = TRUE;
                        if($this->type!==self::CONSUMED_WHITESPACE)
                                return $res;
                }
                // nothing more to consume?
                if($this->_rest==='') {
			$this->type = self::CONSUMED_NOTHING;
			return FALSE;
		}

                // what have we arrived at?

                // whitespace?
                if(($l = strlen($newRest = ltrim($this->_rest))) !== ($tl=strlen($this->_rest))) {
                        $whitespace = substr($this->_rest, 0, $tl-$l);
                        $this->_rest = $newRest;
                        $this->type = self::CONSUMED_WHITESPACE;
                        $this->_l -= $tl-$l;
                        return $whitespace;
                }
                $l = mb_strlen($this->_rest, 'UTF-8');
                $c = mb_substr($this->_rest, 0, 1);

                // quotes?
                if($c=='"' || $c=="'")
                        return $this->_consumeQuotes();

                // word?
                if(strlen($c)>1 || strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_', $c)!==false)
                        return $this->_consumeWord();

                // number?
                if(strpos('0123456789', $c)!==false)
                        return $this->_consumeNumber();

                // everything else is symbols
                $this->type = self::CONSUMED_SYMBOLS;
                $result = '';
                while(strlen($c)==1 && strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"\'', $c)===FALSE && trim($c)!=='') {
                        $result .= $c;
                        $this->_rest = mb_substr($this->_rest, 1, $this->_l, 'UTF-8');
                        $this->_l--;
                        $c = mb_substr($this->_rest, 0, 1, 'UTF-8');
                }
                if(strlen($result)>0) return $result;
                return FALSE;
        }

        protected function _consumeWord() {
                $this->type = self::CONSUMED_WORD;
                $result = '';
                while($this->_l>=0 && (($c = mb_substr($this->_rest, 0, 1))!=='') && (strlen($c)>1 || strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_', $c)!==false)) {
                        $result .= $c;
                        $this->_rest = mb_substr($this->_rest, 1, $this->_l, 'UTF-8');
                        $this->_l--;
                }
                return $result;
        }

        protected function _consumeQuotes() {
                $this->type = self::CONSUMED_QUOTED;
                $q = mb_substr($this->_rest, 0, 1, 'UTF-8');
                $start = 1;
                while(($o = mb_strpos($this->_rest, $q, $start, 'UTF-8')) !== false) {
                        // I have a possible end
                        if(mb_substr($this->_rest, $o-1, 1)=="\\") { $start = $o+1; continue; }
                        $result = mb_substr($this->_rest, 1, $o-1, 'UTF-8');
                        $this->_rest = mb_substr($this->_rest, $o+1, $this->_l, 'UTF-8');
                        $this->_l -= $o+1;
                        return str_replace("\\".$q, $q, $result);
                }
        }

        protected function _consumeNumber() {
                $this->type = self::CONSUMED_INTEGER;
                $result = '';
                $dotTaken = FALSE;
                while(($c=mb_substr($this->_rest, 0, 1))!=='' && (strpos('0123456789'.($dotTaken?'':'.'), $c)!==FALSE)) {
                        if($c=='.') { $dotTaken = TRUE; $this->type = self::CONSUMED_FLOAT; }
                        $result .= $c;
                        $this->_rest = mb_substr($this->_rest, 1, $this->_l, 'UTF-8');
                        $this->_l--;
                }
                return $result;
        }
}

