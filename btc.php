<?php

    class CNGN {

        public $FO = [];
        public $sigma = "";
        public $condition = "";
        public $results = [];
        public $messages = [];
        public $x_of = [];
        public $fn_x = [];
        public $f = "";
        public $g = "";
        public $vars;
        public $seq = [];
        function __construct(float $index_cnt)
        {
            $this->messages[] = "Error: " ;
            $this->register_vars($index_cnt);
        }

        public function string_replace_x($replacements, &$template) {
            $replacements = $this->vars;
            return preg_replace_callback('/{x(.+?)}/',
                     function($matches) use ($replacements) {
                return $replacements[$matches[1]];
            }, $template);
        }

        public function string_replace_n($replacements, &$template) {
            return preg_replace_callback('/{z(.+?)}/',
                     function($matches) use ($replacements) {
                return $replacements[$matches[1]];
            }, $template);
        }

        public function string_replace_b(string &$template, array $sequence) {
            $this->seq = $sequence;
            return preg_replace_callback('/{c(.+?),(.+?)}/',
                     function($matches) use ($sequence) {
                $this->string_replace_x($sequence,$matches[2]);
                if (!is_numeric($matches[2]))
                {
                    $this->msg(0,"There must be 2 parameters to {c}. Example: {c101101,3}.<br>Yours: {c".$matches[1].",".$matches[2]."}");
                    exit(0);
                }
                if (bindec($matches[1]) > 55 && bindec($matches[1]) < 58)
                {
                        return $this->calculus((string)$matches[1], $this->seq);
                }
                else if (bindec($matches[1]) == 58)
                {
                    if (is_array($this->seq[0]))
                    {
                        return $this->calculus((string)$matches[1], $this->seq);
                    }
                    else
                    {
                        return $this->calculus((string)$matches[1], [$this->seq]);
                    }
                }
                return $this->x((string)$matches[1], (int)trim($matches[2], " "));
            }, $template);
        }

        public function load_vars(array $placements) : void
        {
            foreach ($placements as $k => $v)
            {
                $hex = dechex($k);
                $this->vars[$hex] = $v;
            }
            return;
        }

        public function load_fn_x(array $placements) : void
        {
            foreach ($placements as $k => $v)
            {
                $hex = dechex($k);
                $this->fn_x[$hex] = $v;
            }
            return;
        }

        public function register_vars($index_cnt)
        {
            $x = 0;
            while ($x < $index_cnt)
            {
                $hex = dechex($x);
                $this->vars[$hex] = false;
                $x++;
            }
        }

        public function register_fn_x($index_cnt)
        {
            $x = 0;
            while ($x < $index_cnt)
            {
                $hex = dechex($x);
                $this->fn_x[$hex] = false;
                $x++;
            }
        }

        public function add_vars(float $index_cnt)
        {
            $x = count($this->vars);
            $s = $x;
            do
            {
                $hex = dechex($s);
                $this->vars[$hex] = false;
                $s++;
            } while ($s < $x + $index_cnt);
        }

        public function add_fn_x(float $index_cnt)
        {
            $x = count($this->fn_x);
            $s = $x;
            do
            {
                $hex = dechex($s);
                $this->fn_x[$hex] = false;
                $s++;
            } while ($s < $x + $index_cnt);
        }

        /*
        *
        * Parse string of {xFA} x-hex values
        * and replace with $vars values 
        * 
        */
        public function mathParse(string $formula, array $sequence = [])
        {
            if (count($sequence) == 0)
                $sequence = $this->vars;
            if ($formula == "")
            {
                $this->msg(0, 'Empty string given, try mathParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            $string = $formula;
            $x = 0;
            $string = $this->stringParse($string);
            // Parse {x00}
            while (strpos($string, "{c") !== false)
            {
                $string = $this->string_replace_b($string, $sequence);
            }
            return eval("return $string;");
        }

        /*
        *
        * Parse string of {xFA} x-hex values
        * and replace with $vars values 
        * 
        */
        public function stringParse(string $string)
        {
            if ($string == "")
            {
                $this->msg(0, 'Empty string given, try stringParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
                return false;
            }
            while (strpos($string, "{x") !== false)
            {
                $string = $this->string_replace_x($this->vars, $string);
            }
            return $string;
        }

        /*
        *
        * $string .= message at $msg_id
        * 
        */
        public function msg(float $msg_id, string $arb_msg = "")
        {
            echo $this->messages[$msg_id] . $arb_msg;
            return;
        }

        /**
         * the X function. Because the other letters are dumb.
         * 
         * use a space between each binary command
         * 
         */
        private function x(string $j, int $i)
        {
            
            {
                $t = $j;
                if ($t == "000000")   // s1 * s2
                {    return cosh((float)$this->seq[$i]); }
                else if ($t == "000001")   // s1 * s2 
                {    return cos((float)$this->seq[$i]); }
                else if ($t == "000010")   // s1 * s2 
                {    return sinh((float)$this->seq[$i]); }
                else if ($t == "000011")   // s1 * s2 
                {    return sin((float)$this->seq[$i]); }
                else if ($t == "000100")   // s1 * s2 
                {    return tanh((float)$this->seq[$i]); }
                else if ($t == "000101")   // s1 * s2 
                {    return tan((float)$this->seq[$i]); }
                else if ($t == "000110")   // secant
                {    return 1 / sin((float)$this->seq[$i]); }
                else if ($t == "000111")   // cosecant
                {    return 1 / cos((float)$this->seq[$i]); }
                else if ($t == "001000")   // cotangent
                {    return 1 / tan((float)$this->seq[$i]); }
                else if ($t == "001001")   // arcsine
                {    return asin((float)$this->seq[$i]); }
                else if ($t == "001010")   // arccosine
                {    return acos((float)$this->seq[$i]); }
                else if ($t == "001011")   // arctangent
                {    return atan((float)$this->seq[$i]); }
                else if ($t == "001100")   // inverse sine
                {    return 1 / (1 / cos((float)$this->seq[$i])); }
                else if ($t == "001101")   // inverse cosine
                {    return sin((float)$this->seq[$i]) / cos((float)$this->seq[$i]); }
                else if ($t == "001110")   // inverse cotangent
                {    return cos((float)$this->seq[$i]) / sin((float)$this->seq[$i]); }
                else if ($t == "001111")   // constant rule
                {    return 0; }
                else if ($t == "010000")   // s1 * s2 
                {    return $this->sum_rule((float)$this->seq[$i]); }
                else if ($t == "010001")   // s1 - s2
                {    return $this->diff_rule((float)$this->seq[$i]); }
                else if ($t == "010010" && sizeof($this->seq) >= 2)   // s1 ^ s2
                {    return $this->power_rule(array_slice($this->seq,0,2)); }
                else if ($t == "010011")   // s1 * s2
                {    return $this->product_rule((float)$this->seq[$i]); }
                else if ($t == "010100")   // s1 / s2
                {    return $this->quotient_rule((float)$this->seq[$i]); }
                else if ($t == "010101")   // s1 * s2
                {    return $this->chain_rule((float)$this->seq[$i]); }
                else if ($t == "010110")   // ^2
                {    return pow((float)$this->seq[$i], (float)$this->seq[$i+1]); }
                else if ($t == "010111")   // s1 + s2
                {    return " + "; }
                else if ($t == "011000")   // s1 - s2
                {    return " - "; }
                else if ($t == "011001")   // s1 * s2
                {    return " * "; }
                else if ($t == "011010")   // $s / $s2
                {    return " / "; }
                else if ($t == "011100")   // s1 > s2
                {    return $this->condition .= ((float)$this->seq[$i] > $this->seq[$i+1]); }
                else if ($t == "011101")   // s1 < s2
                {    return $this->condition .= ((float)$this->seq[$i] < $this->seq[$i+1]); }
                else if ($t == "011110")   // s1 >= s2
                {    return $this->condition .= ((float)$this->seq[$i] >= $this->seq[$i+1]); }
                else if ($t == "011111")   // s1 <= s2
                {    return $this->condition .= ((float)$this->seq[$i] <= $this->seq[$i+1]); }
                else if ($t == "100000")   // s1 != s2
                {    return $this->condition .= ((float)$this->seq[$i] != $this->seq[$i+1]); }
                else if ($t == "100001")   // s1 == s2
                {    return $this->condition .= ((float)$this->seq[$i] == $this->seq[$i+1]); }
                else if ($t == "100010")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] == $this->seq[$i+1]); }
                else if ($t == "100011")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] != $this->seq[$i+1]); }
                else if ($t == "100100")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] > $this->seq[$i+1]); }
                else if ($t == "100101")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] < $this->seq[$i+1]); }
                else if ($t == "100110")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] >= $this->seq[$i+1]); }
                else if ($t == "100111")   // s1 && s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) && $this->seq[$i] <= $this->seq[$i+1]); }
                else if ($t == "101000")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] == $this->seq[$i+1]); }
                else if ($t == "101001")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] != $this->seq[$i+1]); }
                else if ($t == "101010")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] > $this->seq[$i+1]); }
                else if ($t == "101011")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] < $this->seq[$i+1]); }
                else if ($t == "101100")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] >= $this->seq[$i+1]); }
                else if ($t == "101101")   // s1 || s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) || $this->seq[$i] <= $this->seq[$i+1]); }
                else if ($t == "101110")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] == $this->seq[$i+1]); }
                else if ($t == "101111")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] != $this->seq[$i+1]); }
                else if ($t == "110000")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] > $this->seq[$i+1]); }
                else if ($t == "110001")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] < $this->seq[$i+1]); }
                else if ($t == "110010")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] >= $this->seq[$i+1]); }
                else if ($t == "110011")   // s1 ^ s2
                {    return $this->condition .= ((bool)substr($this->condition,-1) ^ $this->seq[$i] <= $this->seq[$i+1]); }
                else if ($t == "110100")    // factorial
                {    return $this->mathFact((float)$this->seq[$i]); }
                else if ($t == "110101")   // ln()
                {    return exp((float)$this->seq[$i]); }
                else if ($t == "110110")   // ln()
                {    return log((float)$this->seq[$i]); }
                else if ($t == "110111")   // log_base()
                {    return log((float)$this->seq[$i], (float)$this->seq[$i+1]); }
                else if ($t == "111000")   // integrand()
                {    return $this->calculus("000000", $this->seq); }
                else if ($t == "111001")   // integral()
                {    return $this->calculus("000001", $this->seq); }
                else if ($t == "111010")   // find_integral()
                {    return $this->calculus("000010", $this->seq); }
                else if ($t == "111010")   // find_integral()
                {    return $this->calculus("000011", $this->seq); }
                else if ($t == "111011")   // cond_prob() // uses $this->condition
                {    return $this->cond_prob($this->seq[$i]); }
                else if ($t == "111100")   // bayes_prob() // uses $this->condition as prior probability
                {    return $this->bayes_prob($this->seq[$i], $this->seq[$i+1]); }
                else if ($t == "111101")   // is_prime
                {    return $this->is_prime($this->seq[$i]); }
                else if ($t == "111110")   // XOR
                {    return $this->bitw_cmp($this->seq); }

            }
            if (strlen($this->sigma) > 0)
                return eval("return $this->sigma;");
        }

        public function bitw_cmp(array $lr)
        {
            $aw = $lr[0];
            $lb = $lr[1];
            $rb = $lr[2];
            if (decbin($lr[1]) == $lr[1])
                $lb = bindec($lr[1]);
            if (decbin($lr[2]) == $lr[2])
                $rb = bindec($lr[2]);
                if ($aw == "00")
                    return $lb ^ $rb;
                else if ($aw == "01")
                    return $lb & $rb;
                else if ($aw == "10")
                    return $lb | $rb; 
                else if ($aw == "11")
                    return $lb >> $rb;
                else if ($aw == "100")
                    return $lb << $rb;
        }

        public function cond_prob(string $vals)
        {
            $PA = substr_count($this->condition,"1");
            $PB = substr_count($vals,"1");

            return (int)$PA/$PB;
        }

        public function bayes_prob(string $AB, string $A)
        {
            $PB = substr_count($this->condition,"1") / strlen($this->condition);
            $PA = substr_count($A,"1") / strlen($A);

            return ($AB * $PB) / $PA;
        }

        public function is_prime($number)
        {
            // 1 is not prime
            if ( $number == 1 ) {
                return false;
            }
            // 2 is the only even prime number
            if ( $number == 2 ) {
                return true;
            }
            // square root algorithm speeds up testing of bigger prime numbers
            $x = sqrt($number);
            $x = floor($x);
            for ( $i = 2 ; $i <= $x ; ++$i ) {
                if ( $number % $i == 0 ) {
                    break;
                }
            }
         
            if( $x == $i-1 ) {
                return true;
            } else {
                return false;
            }
        }

        public function calculus(string $t, array $sequence)
        {
            {
                if ($t == "000000")   // integrand
                {    return $this->integrand($sequence); }
                else if ($t == "000001")   // integral // Make seq[$i] a subarray & seq[1] the average height of perimeter 
                {    return $this->integral($sequence); }
                else if ($t == "000010")   // integral 
                {    return $this->find_integral($sequence); }
                else if ($t == "000011")   // integral 
                {    return $this->differential($sequence); }
            }
        }

        public function integral(array $sequence)
        {
            $length = array_sum($sequence);
            $avg_height = array_sum($sequence) / count($sequence);
            return ($length * $avg_height);
        }

        /**
         * 
         * Integrand ([[secant, y = base/min, height = base/max], [sec, y, high]])
         * 
         */
        public function find_integral(array $sequence)
        {
            $h = [];
            $sum = [];
            foreach ($sequence as $k => $v)
            {
                $midpoint = (int)$v[0] / 2; 
                $incise = abs((int)$v[2] - (int)$v[1]);
                $perimeter = ($midpoint * 2) + ($incise * 2);
                $length = $perimeter / 2;
                $length += $incise / 2;
                $sum [] = $length;
                $h [] = (int)$v[2];
            }
            $integral = $this->integral($sum);
            return $integral;
        }


        public function zeta_loss(int $sub_ = 0, int $add_ = 0, int $flip_ = 0)
        {
            $pi = 3.1415926535897932384626433832795;
            $seq = [
                0.618,  0.56418957569775374239, $pi, 3
            ];
            $tr = [];
            $tf = 1;
            $cnt = 0;
            $exp = 0;
            $c = 1;
            for($z = 0 ; count($tr) < 50 ; $z+=1)
            {
                $seq[3] = $this->integrand($seq);
                echo $seq[3] . " ";
                $seq[3] += pow(($z)*0.56418957569775374239,2);
                $tf = ceil(($seq[3]+4)/($tf+1));
                $this->is_prime($tf) ? array_push($tr, $tf) : false;
                echo $this->is_prime($tf) ? '<b style="color:darkblue">'.($tf).'</b> ' : "!";
                $tr = array_unique($tr);
                $c++;
            }
            echo count($tr) . " $c/$tf " . $z;
        }
        
        /**
         * 
         * Integrand ([secant, y = base/min, height = base/max])
         * 
         */
        public function integrand(array $sequence)
        {
            $midpoint = $sequence[0] / 2; 
            $incise = abs($sequence[2] - $sequence[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;
			$length--;
            return $length;
        }

        /**
         * 
         * Differential ([secant, y = base/min, height = base/max])
         * 
         */
        public function differential(array $sequence)
        {
            $midpoint = $sequence[0] / 2; 
            $incise = abs($sequence[2] - $sequence[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;

            
            $midpoint = $sequence[0] / $length; 
            $incise = abs($sequence[2] - $sequence[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;

            return $length;
        }

        /**
         * 
         * Derive ([secant, y = base/min, height = base/max])
         * 
         */ 
        public function derive(array $sequence)
        {
            $midpoint = $sequence[0] / $sequence[3]; 
            $incise = abs($sequence[2] - $sequence[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;
            return $sequence[3] / $length;
        }
        /**
         * 
         * Factorials
         * 
         */
        function mathFact( $s )
        {
            $r = (int) $s;

            if ( $r < 2 )
                $r = 1;
            else {
                for ( $i = $r-1; $i > 1; $i-- )
                    $r = $r * $i;
            }
            return $r;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function f(float $x)
        {
            if ($this->f_ == "")
            {
                $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable.");
                exit(0);
            }
            $v = ($this->stringParse($this->f_));
            return eval("return $v;");
        }

        /*
        *
        * set function of f() -- Use {x} wherever you need your variable
        * 
        */
        public function set_f_of(string $ev)
        {
            $this->f_ = $ev;
        }

        /*
        *
        * get function of g() -- Use {x} wherever you need your variable
        * 
        */
        public function g(float $x)
        {
            if ($this->g_ == "")
            {
                $this->msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
                exit(0);
            }
            $v = ($this->stringParse($this->g_));

            return eval("return $v;");
        }

        /*
        *
        * set function of g()
        * 
        */
        public function set_g_of(string $ev)
        {
            $this->g_ = $ev;
        }

        /*
        *
        * Condition d/dx [f(x)+g(x)]
        * 
        */
        public function sum_rule(float $sequence)
        {
            $tmp1 = $this->f((float)$sequence);
            $tmp2 = $this->g((float)$sequence);

            return $tmp1 + $tmp2;
        }

        /*
        *
        * Condition d/dx [f(x)-g(x)]
        * 
        */
        public function diff_rule(float $sequence)
        {
            $tmp1 = $this->f((float)$sequence);
            $tmp2 = $this->g((float)$sequence);
            
            return $tmp1 - $tmp2;
        }

        /*
        *
        * Condition d/dx [x^n]
        * 
        */
        public function power_rule(array $sequence)
        {
            $tmp = $sequence;

            return (float)(pow((int)$tmp[0],(int)$tmp[1]-1) * (float)$tmp[1]);
        }

        /*
        *
        * Condition d/dx [f(x)g(x)]
        * 
        */
        public function product_rule(float $sequence)
        {

            // f'(x)                // f(x)
            $tmp_f = $this->f((float)$sequence);
            // g'(x)                // g(x)
            $tmp_g = $this->g((float)$sequence);
            
            $tmp_ff = $this->f((float)$tmp_f);
            $tmp_gg = $this->g((float)$tmp_g);
            $final1a = $tmp_ff * $tmp_g;
            $final1b = $tmp_f * $tmp_gg;
            return $final1b + $final1a;
        }

        /*
        *
        * Condition d/dx [f(g(x))]
        * 
        */
        public function chain_rule(float $sequence)
        {
            
            // g'(x)                // g(x)
            $tmp_g = (float)($this->g($this->seq[0]));
            
            // f'(x)                // f(x)
            $tmp_f = (float)($this->f($tmp_g));

            $tmp_ff = ($this->f($tmp_f));
            $tmp_gg = ($this->g($tmp_f));

            return $tmp_ff * $tmp_gg;
        }

        /*
        *
        * Condition d/dx [f(x)/g(x)]
        * 
        */
        public function quotient_rule(float $sequence)
        {

            $tmp_f = (float)$this->f((float)$this->sequence);
            $tmp_g = (float)$this->g((float)$this->sequence);

            $tmp_ff = (float)$this->f($tmp_f);
            $tmp_gg = (float)$this->f($tmp_g);
            
            $final1a = $tmp_ff * $tmp_g;
            $final1b = $tmp_f * $tmp_gg;
            
            $final2 = $final1a * $final1b;
            $answer = $final2 / ($tmp_g * $tmp_g);
            return ($answer);
        }
        
        function bitcoin(string $btc_csv, int $day_cnt, $data_column = 1, $date_column = 0)
        {
            $sf = fopen("$btc_csv","r");
            $seq = [];
            $data = [];
            fgets($sf);
            fgets($sf);
            $day_before = 0;
            $date_1 = 0;
            $y = 1;
            $base = 0;
            while (($data = fgetcsv($sf,300,','," ")) !== FALSE)
            {
                if ($y < 2)
				{
					$y+=$day_cnt;
					$day_before = $data[$data_column];
					continue;
				}
                $date_1 = $day_before;
                $seq[] = [ ($y), $date_1, $day_before, $data[$date_column] ];
                $day_before = $data[$data_column];
                $y+=$day_cnt;
            }
            fclose($sf);

            $string = "<table valign='top' style='position:width:100%relative;top-margin:0px'>";
            $string .= "<tr><td>Long Form Date </td><td> Integrand </td><td> Differential </td><td> Differential </td><td> Integral </td><td> Price</td><td>Low</td><td>RB</td></tr>";
            $y = 1;
            $vals = [];
            $x = 1;
            $exp = 1;
            $out = 1;
            $inc_real = 0;
             $inc_imaginary = 0;
			$s = 0;
			$count = 0;
            $inc_last = 0;
            $correct = 0;
            foreach($seq as $key) {
                $vals = $key;
                $inc_real = $vals[1];
                array_pop($vals);
                $vals[] = $this->integrand($key);
                $c = $this->differential($key);
                $real = "";
                $bool1 = true;
                if ($inc_last < $inc_real)
                {
                    $real = "<td>+".$key[1]."</td>";
                    $bool1 = false;
                }
                else
                    $real = "<td>-".$key[1]."</td>";
                $string .= "<tr><td colspan='2'>".$key[3]." </td>" . /*<td> ".$vals[3]. " </td> */"<td> $c  </td><td>". $this->derive($vals) . " </td><td> ".$this->integral($key)."</td>$real";
                $lo = $this->derive($vals) / $vals[3] / $c;
                $lo *= $this->derive($vals);
                while ($lo <= 0.0999)
                    $lo *= 10;
                // $out = ($out <= 0) ? 100 : $out;
                $short_low = abs(($lo));
                $short_low = (($lo * $vals[2] / 2000) - $vals[3] );
                $short_low = ($base + round($short_low / $out,2)*2);// - (1 * $exp));
                $exp = 1;
                if ($short_low > pow(10, $exp) && $exp < 3) {
                    $out = pow(10, $exp++);
                }
                // $short_low = $short_low / 10 * (abs(++$count)%7) + 1; 
                // $out = round(($out / 10),2);
                $bool2 = false;
                $same = "";
                if ($short_low < $inc_imaginary)
                {
                    $string .= "<td>-$short_low</td>";
                    $bool2 = true;
                }
                else 
                    $string .= "<td>+$short_low</td>";
                // $string .= (($short_low + abs($inc_real - $inc_last)) > $inc_imaginary) ? "<td>+".abs($short_low + abs($inc_real - $inc_last))."</td>" : "<td>-".($short_low + abs($inc_real - $inc_last))."</td>";
                // $bool2 = ($short_low < $inc_imaginary) ? true : false;
                if ($bool2 == $bool1)
                {    
                    $string .= "<td style='color:red;background-color:blue'>" . (($inc_last - $inc_real)) . "</td></tr>";
                    $correct++;
                }
                else
                    $string .= "<td style='color:blue;background-color:red'>" . (($inc_last - $inc_real)) . "</td></tr>";
                $same = "";
                $inc_imaginary = $short_low;
                $inc_last = $inc_real;
                $x += $day_cnt;
            }
            $base = $short_low;
            $str = "";
            // $vals[0] = $z = $x;
            // $key = $vals;
			// $string .= "<tr><td colspan='8'>".($correct/sizeof($seq)) . "</td></tr>";
            for ($x = 0 ; $x < $day_cnt*50 ; )
            {
                if ($x == 0)
                    $vals = $key;
                    
                $inc_real = $vals[1];
                $vals[] = $this->integrand($key);
                $c = 2; //$this->differential($key);
                $real = "";
                $bool1 = true;
                $vals[3] += 8;
                if ($inc_last < $inc_real)
                {
                    $real = "<td>+".$inc_last."</td>";
                    $bool1 = false;
                }
                else
                    $real = "<td>-".$inc_last."</td>";
                $string .= "<tr><td>".$vals[3]."</td><td> ".$vals[3]." </td><td> $c  </td><td>". ($this->derive($vals)) . " </td><td> ".$this->integral($vals)."</td>$real";
                $lo = ($this->derive($vals)) / $vals[3] / $c;
                $lo *= $this->derive($vals);
                while ($lo <= 0.0999)
                    $lo *= 10;
                // $out = ($out <= 0) ? 100 : $out;
                $short_low = abs(($lo));
                $short_low = (($lo * $vals[2] / 40) - $vals[3] );
                $short_low = ($base + round($short_low / $out,2)*2);// - (1 * $exp));
                $exp = 1;
                if ($short_low > pow(100, $exp) && $exp < 3) {
                    $out = pow(100, $exp++);
                }
                // $short_low = $short_low / 10 * (abs(++$count)%7) + 1; 
                // $out = round(($out / 10),2);
                $bool2 = true;
                $same = "";
                if ($short_low < $inc_imaginary)
                {
                    $string .= "<td>+$short_low</td>";
                    $bool2 = false;
                }
                else 
                    $string .= "<td>-$short_low</td>";
                // $string .= (($short_low + abs($inc_real - $inc_last)) > $inc_imaginary) ? "<td>+".abs($short_low + abs($inc_real - $inc_last))."</td>" : "<td>-".($short_low + abs($inc_real - $inc_last))."</td>";
                // $bool2 = ($short_low < $inc_imaginary) ? true : false;
                if ($bool2 == $bool1)
                {    
                    $string .= "<td style='color:red;background-color:blue'>" . (($inc_last - $inc_real)) . "</td></tr>";
                    $correct++;
                }
                else
                    $string .= "<td style='color:blue;background-color:red'>" . (($inc_last - $inc_real)) . "</td></tr>";
                $same = "";
                $inc_imaginary = $short_low;
                $inc_last = $inc_real;
                $x += $day_cnt;
                $vals = [ ($x), $short_low, $vals[1], $vals[3] ];
            }
            $string .= $str . "</table>";
            $string .= "<br>Total Integral";
            $string .= "<br> &nbsp; &nbsp;" . $this->find_integral($seq);
            $string .= "<br>";
            echo $string;
        }
    }
    $next = new CNGN(5);
    $after = new CNGN(5);
    // 14 for Bitcoin
    // 150 for ETH
    // Doge
    echo "<table style='position:relative;margin-top:0px'><tr><td style='width:50%;position:absolute;margin-top:0px'>";
    $next->bitcoin("ETH-USD.csv", 15);
    echo "</td><td style='width:50%;position:absolute;margin-left:800;margin-top:0px'>";
    $after->bitcoin("eth-usdlong.csv", 15);
    echo "</td></tr></table>";