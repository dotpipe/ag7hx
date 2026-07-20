<?php

class CNGN
{

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
        $this->messages[] = "Error: ";
        $this->register_vars($index_cnt);
    }

    public function string_replace_x($replacements, &$template)
    {
        $replacements = $this->vars;
        return preg_replace_callback(
            '/{x(.+?)}/',
            function ($matches) use ($replacements) {
                return $replacements[$matches[1]];
            },
            $template
        );
    }

    public function string_replace_n($replacements, &$template)
    {
        return preg_replace_callback(
            '/{z(.+?)}/',
            function ($matches) use ($replacements) {
                return $replacements[$matches[1]];
            },
            $template
        );
    }

    public function string_replace_b(string &$template, array $sequence)
    {
        $this->seq = $sequence;
        return preg_replace_callback(
            '/{c(.+?),(.+?)}/',
            function ($matches) use ($sequence) {
                $this->string_replace_x($sequence, $matches[2]);
                if (!is_numeric($matches[2])) {
                    $this->msg(0, "There must be 2 parameters to {c}. Example: {c101101,3}.<br>Yours: {c" . $matches[1] . "," . $matches[2] . "}");
                    exit(0);
                }
                if (bindec($matches[1]) > 55 && bindec($matches[1]) < 58) {
                    return $this->calculus((string) $matches[1], $this->seq);
                } else if (bindec($matches[1]) == 58) {
                    if (is_array($this->seq[0])) {
                        return $this->calculus((string) $matches[1], $this->seq);
                    } else {
                        return $this->calculus((string) $matches[1], [$this->seq]);
                    }
                }
                return $this->x((string) $matches[1], (int) trim($matches[2], " "));
            },
            $template
        );
    }

    public function load_vars(array $placements): void
    {
        foreach ($placements as $k => $v) {
            $hex = dechex($k);
            $this->vars[$hex] = $v;
        }
        return;
    }

    public function load_fn_x(array $placements): void
    {
        foreach ($placements as $k => $v) {
            $hex = dechex($k);
            $this->fn_x[$hex] = $v;
        }
        return;
    }

    public function register_vars($index_cnt)
    {
        $x = 0;
        while ($x < $index_cnt) {
            $hex = dechex($x);
            $this->vars[$hex] = false;
            $x++;
        }
    }

    public function register_fn_x($index_cnt)
    {
        $x = 0;
        while ($x < $index_cnt) {
            $hex = dechex($x);
            $this->fn_x[$hex] = false;
            $x++;
        }
    }

    public function add_vars(float $index_cnt)
    {
        $x = count($this->vars);
        $s = $x;
        do {
            $hex = dechex($s);
            $this->vars[$hex] = false;
            $s++;
        } while ($s < $x + $index_cnt);
    }

    public function add_fn_x(float $index_cnt)
    {
        $x = count($this->fn_x);
        $s = $x;
        do {
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
        if ($formula == "") {
            $this->msg(0, 'Empty string given, try mathParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
            return false;
        }
        $string = $formula;
        $x = 0;
        $string = $this->stringParse($string);
        // Parse {x00}
        while (strpos($string, "{c") !== false) {
            $string = $this->string_replace_b($string, $sequence);
        }
        return eval ("return $string;");
    }

    /*
     *
     * Parse string of {xFA} x-hex values
     * and replace with $vars values 
     * 
     */
    public function stringParse(string $string)
    {
        if ($string == "") {
            $this->msg(0, 'Empty string given, try stringParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
            return false;
        }
        while (strpos($string, "{x") !== false) {
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
    { {
            $t = $j;
            if ($t == "000000") // s1 * s2
            {
                return cosh((float) $this->seq[$i]);
            } else if ($t == "000001") // s1 * s2 
            {
                return cos((float) $this->seq[$i]);
            } else if ($t == "000010") // s1 * s2 
            {
                return sinh((float) $this->seq[$i]);
            } else if ($t == "000011") // s1 * s2 
            {
                return sin((float) $this->seq[$i]);
            } else if ($t == "000100") // s1 * s2 
            {
                return tanh((float) $this->seq[$i]);
            } else if ($t == "000101") // s1 * s2 
            {
                return tan((float) $this->seq[$i]);
            } else if ($t == "000110") // secant
            {
                return 1 / sin((float) $this->seq[$i]);
            } else if ($t == "000111") // cosecant
            {
                return 1 / cos((float) $this->seq[$i]);
            } else if ($t == "001000") // cotangent
            {
                return 1 / tan((float) $this->seq[$i]);
            } else if ($t == "001001") // arcsine
            {
                return asin((float) $this->seq[$i]);
            } else if ($t == "001010") // arccosine
            {
                return acos((float) $this->seq[$i]);
            } else if ($t == "001011") // arctangent
            {
                return atan((float) $this->seq[$i]);
            } else if ($t == "001100") // inverse sine
            {
                return 1 / (1 / cos((float) $this->seq[$i]));
            } else if ($t == "001101") // inverse cosine
            {
                return sin((float) $this->seq[$i]) / cos((float) $this->seq[$i]);
            } else if ($t == "001110") // inverse cotangent
            {
                return cos((float) $this->seq[$i]) / sin((float) $this->seq[$i]);
            } else if ($t == "001111") // constant rule
            {
                return 0;
            } else if ($t == "010000") // s1 * s2 
            {
                return $this->sum_rule((float) $this->seq[$i]);
            } else if ($t == "010001") // s1 - s2
            {
                return $this->diff_rule((float) $this->seq[$i]);
            } else if ($t == "010010" && sizeof($this->seq) >= 2) // s1 ^ s2
            {
                return $this->power_rule(array_slice($this->seq, 0, 2));
            } else if ($t == "010011") // s1 * s2
            {
                return $this->product_rule((float) $this->seq[$i]);
            } else if ($t == "010100") // s1 / s2
            {
                return $this->quotient_rule((float) $this->seq[$i]);
            } else if ($t == "010101") // s1 * s2
            {
                return $this->chain_rule((float) $this->seq[$i]);
            } else if ($t == "010110") // ^2
            {
                return pow((float) $this->seq[$i], (float) $this->seq[$i + 1]);
            } else if ($t == "010111") // s1 + s2
            {
                return " + ";
            } else if ($t == "011000") // s1 - s2
            {
                return " - ";
            } else if ($t == "011001") // s1 * s2
            {
                return " * ";
            } else if ($t == "011010") // $s / $s2
            {
                return " / ";
            } else if ($t == "011100") // s1 > s2
            {
                return $this->condition .= ((float) $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "011101") // s1 < s2
            {
                return $this->condition .= ((float) $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "011110") // s1 >= s2
            {
                return $this->condition .= ((float) $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "011111") // s1 <= s2
            {
                return $this->condition .= ((float) $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "100000") // s1 != s2
            {
                return $this->condition .= ((float) $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "100001") // s1 == s2
            {
                return $this->condition .= ((float) $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "100010") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "100011") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "100100") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "100101") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "100110") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "100111") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "101000") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "101001") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "101010") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "101011") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "101100") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "101101") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "101110") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "101111") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "110000") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "110001") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "110010") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "110011") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "110100") // factorial
            {
                return $this->mathFact((float) $this->seq[$i]);
            } else if ($t == "110101") // ln()
            {
                return exp((float) $this->seq[$i]);
            } else if ($t == "110110") // ln()
            {
                return log((float) $this->seq[$i]);
            } else if ($t == "110111") // log_base()
            {
                return log((float) $this->seq[$i], (float) $this->seq[$i + 1]);
            } else if ($t == "111000") // integrand()
            {
                return $this->calculus("000000", $this->seq);
            } else if ($t == "111001") // integral()
            {
                return $this->calculus("000001", $this->seq);
            } else if ($t == "111010") // find_integral()
            {
                return $this->calculus("000010", $this->seq);
            } else if ($t == "111010") // find_integral()
            {
                return $this->calculus("000011", $this->seq);
            } else if ($t == "111011") // cond_prob() // uses $this->condition
            {
                return $this->cond_prob($this->seq[$i]);
            } else if ($t == "111100") // bayes_prob() // uses $this->condition as prior probability
            {
                return $this->bayes_prob($this->seq[$i], $this->seq[$i + 1]);
            } else if ($t == "111101") // is_prime
            {
                return $this->is_prime($this->seq[$i]);
            } else if ($t == "111110") // XOR
            {
                return $this->bitw_cmp($this->seq);
            }

        }
        if (strlen($this->sigma) > 0)
            return eval ("return $this->sigma;");
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
        $PA = substr_count($this->condition, "1");
        $PB = substr_count($vals, "1");

        return (int) $PA / $PB;
    }

    public function bayes_prob(string $AB, string $A)
    {
        $PB = substr_count($this->condition, "1") / strlen($this->condition);
        $PA = substr_count($A, "1") / strlen($A);

        return ($AB * $PB) / $PA;
    }

    public function is_prime($number)
    {
        // 1 is not prime
        if ($number == 1) {
            return false;
        }
        // 2 is the only even prime number
        if ($number == 2) {
            return true;
        }
        // square root algorithm speeds up testing of bigger prime numbers
        $x = sqrt($number);
        $x = floor($x);
        for ($i = 2; $i <= $x; ++$i) {
            if ($number % $i == 0) {
                break;
            }
        }

        if ($x == $i - 1) {
            return true;
        } else {
            return false;
        }
    }

    public function calculus(string $t, array $sequence)
    { {
            if ($t == "000000") // integrand
            {
                return $this->integrand($sequence);
            } else if ($t == "000001") // integral // Make seq[$i] a subarray & seq[1] the average height of perimeter 
            {
                return $this->integral($sequence);
            } else if ($t == "000010") // integral 
            {
                return $this->find_integral($sequence);
            } else if ($t == "000011") // integral 
            {
                return $this->differential($sequence);
            }
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
        foreach ($sequence as $k => $v) {
            $midpoint = (int) $v[0] / 2;
            $incise = abs((int) $v[2] - (int) $v[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;
            $sum[] = $length;
            $h[] = (int) $v[2];
        }
        $integral = $this->integral($sum);
        return $integral;
    }


    public function zeta_loss(int $sub_ = 0, int $add_ = 0, int $flip_ = 0)
    {
        $pi = 3.1415926535897932384626433832795;
        $seq = [
            0.618,
            0.56418957569775374239,
            $pi,
            3
        ];
        $tr = [];
        $tf = 1;
        $cnt = 0;
        $exp = 0;
        $c = 1;
        for ($z = 0; count($tr) < 50; $z += 1) {
            $seq[3] = $this->integrand($seq);
            echo $seq[3] . " ";
            $seq[3] += pow(($z) * 0.56418957569775374239, 2);
            $tf = ceil(($seq[3] + 4) / ($tf + 1));
            $this->is_prime($tf) ? array_push($tr, $tf) : false;
            echo $this->is_prime($tf) ? '<b style="color:darkblue">' . ($tf) . '</b> ' : "!";
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
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
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
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
        $perimeter = ($midpoint * 2) + ($incise * 2);
        $length = $perimeter / 2;
        $length += $incise / 2;


        $midpoint = $sequence[0] / $length;
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
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
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
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
    function mathFact($s)
    {
        $r = (int) $s;

        if ($r < 2)
            $r = 1;
        else {
            for ($i = $r - 1; $i > 1; $i--)
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
        if ($this->f_ == "") {
            $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable.");
            exit(0);
        }
        $v = ($this->stringParse($this->f_));
        return eval ("return $v;");
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
        if ($this->g_ == "") {
            $this->msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
            exit(0);
        }
        $v = ($this->stringParse($this->g_));

        return eval ("return $v;");
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
        $tmp1 = $this->f((float) $sequence);
        $tmp2 = $this->g((float) $sequence);

        return $tmp1 + $tmp2;
    }

    /*
     *
     * Condition d/dx [f(x)-g(x)]
     * 
     */
    public function diff_rule(float $sequence)
    {
        $tmp1 = $this->f((float) $sequence);
        $tmp2 = $this->g((float) $sequence);

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

        return (float) (pow((int) $tmp[0], (int) $tmp[1] - 1) * (float) $tmp[1]);
    }

    /*
     *
     * Condition d/dx [f(x)g(x)]
     * 
     */
    public function product_rule(float $sequence)
    {

        // f'(x)                // f(x)
        $tmp_f = $this->f((float) $sequence);
        // g'(x)                // g(x)
        $tmp_g = $this->g((float) $sequence);

        $tmp_ff = $this->f((float) $tmp_f);
        $tmp_gg = $this->g((float) $tmp_g);
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
        $tmp_g = (float) ($this->g($this->seq[0]));

        // f'(x)                // f(x)
        $tmp_f = (float) ($this->f($tmp_g));

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

        $tmp_f = (float) $this->f((float) $this->sequence);
        $tmp_g = (float) $this->g((float) $this->sequence);

        $tmp_ff = (float) $this->f($tmp_f);
        $tmp_gg = (float) $this->f($tmp_g);

        $final1a = $tmp_ff * $tmp_g;
        $final1b = $tmp_f * $tmp_gg;

        $final2 = $final1a * $final1b;
        $answer = $final2 / ($tmp_g * $tmp_g);
        return ($answer);
    }

    function bitcoin(string $btc_json, int $day_cnt = 15, $data_column = 1, $date_column = 0)
    {
        // CSV file path
        $csvFilePath = $btc_json;

        // Read CSV file
        $file = fopen($csvFilePath, 'r');

        // Array to store CSV data
        $data = [];

        // Read each line of the CSV file
        while (($line = fgetcsv($file)) !== false) {
            // Add each line as an associative array to $data
            $data[] = $line;
        }

        // Close the file
        fclose($file);

        // $sf = file_get_contents("$btc_json");
        // $sf = json_decode($data);
        $seq = [];
        // fgets($sf);
//            fgets($sf);
        $day_before = 0;
        $date_1 = 0;
        $y = 1;
        $base = 0;
        $total_all = 0;
        foreach ($data as $value) {
            // $js = explode(',',$data);
            // foreach ($js as $value) {
            $t_close = $value[4];
            $t_day = $value[0];
            if ($y < 2) {
                $y += $day_cnt;
                $day_before = $t_close;
                continue;
            }
            $date_1 = $day_before;
            $seq[] = [($y), $date_1, $day_before, $t_day];
            $day_before = $t_close;
            $y += $day_cnt;
            $total_all++;
            // }
        }

        //            $string = "<table valign='top' style='background-color:white;z-index:1;position:absolute;width:100%;top-margin:0px;'>";
        $string = "<tr><td style='width:150;margin-top:5px;'>Long Form Date </td><td> Differential </td><td>Integrand</td><td> Integral </td><td>Low</td><td>RB</td></tr>";
        $y = 1;
        $vals = [];
        $x = 0;
        $exp = 1;
        $out = 1;
        $inc_real = 0;
        $inc_imaginary = 0;
        $s = 0;
        $count = 0;
        $inc_last = 0;
        $saved = 0;
        $correct = 0;
        $key = [];
        for ($i = count($seq) - 1; $i >= 0; $i--) {
            $key = $seq[$i];
            $vals = $key;
            $inc_real = $vals[1];
            array_pop($vals);
            $vals[] = $this->integrand($key);
            $c = $this->differential($key);
            $real = "";
            $bool1 = "+";
            if ($i == count($seq) - 1) {
                //                      $real = "<td></td>";
            }
            if (($inc_real) < $inc_last) {
                $real = "<td>+" . $key[1] . "</td>";
                $bool1 = "+";
            } else {
                $real = "<td>-" . $key[1] . "</td>";
                $bool1 = "-";
            }
            $string .= "<tr><td style='width:150;'>" . $key[3] . " </td>" . /*<td> ".$vals[3]. " </td> */ "<td> $c  </td><td>" . $this->derive($vals) . " </td><td> " . $this->integral($key) . "</td>$real";
            $lo = $this->derive($vals) / $vals[3] / $c;
            $lo *= $this->derive($vals) / 2;
            while ($lo <= 0.999)
                $lo *= 1.01;
            $short_low = abs(($lo));
            $short_low = (($lo * intval($vals[2]) / 10) - intval($vals[3]));
            $short_low = ($base + round($short_low / $out, 2) * 2) - (1 * $exp);
            $exp = 1;
            while ($short_low > pow(10, $exp) && $exp < 3) {
                $out = pow(10, $exp++);
            }
            $bool2 = "+";
            if (($short_low * 32.56 < $inc_imaginary / 32.56)) {
                $bool2 = "-";
            }
            if ($bool2 != $bool1)
                $colored = "green";
            else
                $colored = "red";
            if ($bool2 == $bool1)
                $correct++;
            if ($i != count($seq) - 1) {
                $string .= "<td style='color:black;background-color:" . $colored . "'>" . $bool1 . abs(($inc_imaginary - $short_low) / 32.56) . "</td></tr>";
            } else
                $string .= "<td></td></tr>";
            $inc_imaginary = $short_low;
            $inc_last = $inc_real;
        }
        //$string .= "<tr><td colspan='8'>" . round(($correct / $total_all) * 100, 1) . "</td></tr>";
        $base = $short_low;
        $str = $string;
        // $vals[0] = $z = $x;
        $string = "";
        // $saved = [($inc_imaginary - $short_low), ($inc_real)];
        // $key = $vals;
        // $string .= "<tr><td colspan='8'>".($correct/sizeof($seq)) . "</td></tr>";
        for ($x = 0; $x < 45; $x++) // += $day_cnt)
        {
            if ($x == 0)
                $vals = $key;
            else
                $key = $vals;
            $inc_real = $vals[1];
            array_pop($vals);
            $vals[] = $this->integrand($key);
            $c = 2; //$this->differential($key);
            $vals[2] = $this->integral($seq[50 - $x]);
            $bool1 = "+";
            $vals[3] += 8;
            if ((intval($inc_real) / 100) < intval($saved[1]) / 100) {
                $real = "<td>-" . abs(intval($inc_last) / 100 - intval($saved[0]) / 100) . "</td>";
                $bool1 = "-";
            } else
                $real = "<td>+" . abs(intval($inc_last) / 100 + intval($saved[0]) / 100) . "</td>";
            $string .= "<tr><td style='width:150;'>&nbsp; </td>" . /*<td> ".$vals[3]. " </td> */"<td> $c  </td>$real";
            $lo = $this->derive($vals) / intval($vals[3]) / $c;
            $lo *= $this->derive($vals);
            while ($lo <= 0.0999)
                $lo *= 10;
            // $out = ($out <= 0) ? 100 : $out;
            $short_low = abs(($lo));
            $short_low = (($lo * $vals[2] / 10) - $vals[3]);
            $short_low = ($base + round($short_low / $out, 2) * 2); // - (1 * $exp));
            $exp = 1;
            if ($short_low > pow(10, $exp) && $exp < 3) {
                $out = pow(10, $exp++);
            }
            // $short_low = $short_low / 10 * (abs(++$count)%7) + 1;
            // $out = round(($out / 10),2);
            $bool2 = "+";
            $same = "";
            if (($short_low < $inc_imaginary)) {
                //                    $string .= "<td>+$short_low</td>";
                $bool2 = "-";
            }
            if ($bool2 == $bool1)
                $colored = "green";
            else
                $colored = "red";

            $string .= "<td style='color:black;background-color:" . $colored . "'>" . $bool2 . abs(($inc_imaginary - $short_low) / 4 / 100) . "</td></tr>";

            $saved = [($inc_imaginary - $short_low), ($inc_real)];
            $inc_imaginary = $short_low;
            $inc_last = intval($inc_real);
            $x++;
            $vals = [($x), $short_low, $vals[1], $vals[3]];
        }
        $string = $string . $str;
        return [$string, round($correct / count($seq) * 100, 2), $csvFilePath ];
    }
}


// ================================================
// Market Wave Dashboard
// ================================================

$ticker = isset($_GET['symbol'])
    ? strtoupper(trim((string) $_GET['symbol']))
    : 'TSLA';

if (!preg_match('/^[A-Z0-9.\-^]{1,12}$/', $ticker)) {
    $ticker = 'TSLA';
}

$next = new CNGN(5);

$dir = __DIR__ . '/tickers/';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}

$file_path = $dir . $ticker . '.csv';
$display_path = './tickers/' . $ticker . '.csv';
$error_message = '';
$data_note = '';

/**
 * Download fresh Yahoo Finance chart data and convert it to the CSV layout
 * expected by CNGN::bitcoin(): Date,Open,High,Low,Close,Adj Close,Volume.
 */
function fetchYahooChartCsv(string $ticker): array
{
    $encoded = rawurlencode($ticker);
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$encoded}"
        . "?range=1mo&interval=5m&includePrePost=false&events=div%2Csplits";

    $headers = [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)'
    ];

    $body = false;
    $httpCode = 0;

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            return [false, 'Yahoo request failed: ' . ($curlError ?: 'unknown cURL error'), null];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 25,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        $body = @file_get_contents($url, false, $context);

        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int) $match[1];
        }
    }

    if ($body === false || $body === '') {
        return [false, 'Yahoo returned an empty response.', null];
    }

    if ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300)) {
        return [false, "Yahoo returned HTTP {$httpCode}.", null];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        return [false, 'Yahoo returned invalid JSON.', null];
    }

    $error = $json['chart']['error'] ?? null;
    if ($error) {
        $message = $error['description'] ?? $error['code'] ?? 'Unknown Yahoo error.';
        return [false, (string) $message, null];
    }

    $result = $json['chart']['result'][0] ?? null;
    if (!is_array($result)) {
        return [false, 'Yahoo returned no chart data for this symbol.', null];
    }

    $timestamps = $result['timestamp'] ?? [];
    $quote = $result['indicators']['quote'][0] ?? [];
    $adjClose = $result['indicators']['adjclose'][0]['adjclose'] ?? [];
    $meta = $result['meta'] ?? [];

    if (!is_array($timestamps) || count($timestamps) < 100) {
        return [false, 'Yahoo returned too few price points.', $meta];
    }

    $stream = fopen('php://temp', 'w+');
    if ($stream === false) {
        return [false, 'Could not create the local CSV stream.', $meta];
    }

    fputcsv($stream, ['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume']);
    $written = 0;

    foreach ($timestamps as $index => $timestamp) {
        $open = $quote['open'][$index] ?? null;
        $high = $quote['high'][$index] ?? null;
        $low = $quote['low'][$index] ?? null;
        $close = $quote['close'][$index] ?? null;
        $volume = $quote['volume'][$index] ?? 0;

        if (!is_numeric($timestamp) || !is_numeric($close)) {
            continue;
        }

        $adjusted = $adjClose[$index] ?? $close;
        fputcsv($stream, [
            gmdate('Y-m-d H:i:s', (int) $timestamp),
            is_numeric($open) ? $open : $close,
            is_numeric($high) ? $high : $close,
            is_numeric($low) ? $low : $close,
            $close,
            is_numeric($adjusted) ? $adjusted : $close,
            is_numeric($volume) ? $volume : 0,
        ]);
        $written++;
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    if ($written < 100 || !is_string($csv) || strlen($csv) < 1000) {
        return [false, 'Yahoo data could not be converted into enough CSV rows.', $meta];
    }

    return [$csv, '', $meta];
}

$cache_seconds = 300; // refresh Yahoo data every five minutes
$force_refresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$cache_is_stale = !file_exists($file_path)
    || filesize($file_path) < 1000
    || filemtime($file_path) < time() - $cache_seconds;

if ($force_refresh || $cache_is_stale) {
    [$csv, $download_error, $yahoo_meta] = fetchYahooChartCsv($ticker);

    if ($csv !== false) {
        $temporary = $file_path . '.tmp';
        if (@file_put_contents($temporary, $csv, LOCK_EX) !== false) {
            @chmod($temporary, 0664);
            @rename($temporary, $file_path);
            clearstatcache(true, $file_path);
            $data_note = 'Fresh 5-minute Yahoo Finance data loaded.';
        } else {
            $download_error = 'Fresh data was received but could not be saved.';
        }
    }

    if ($csv === false || $download_error !== '') {
        if (file_exists($file_path) && filesize($file_path) >= 1000) {
            $data_note = 'Yahoo refresh failed; displaying the most recent cached data.';
        } else {
            $error_message = $download_error ?: 'Market data could not be downloaded for this symbol.';
        }
    }
} else {
    $data_note = 'Using Yahoo data cached for less than five minutes.';
}

$rets_sofar = ['', 0, $display_path];

if ($error_message === '' && file_exists($file_path)) {
    try {
        $rets_sofar = $next->bitcoin($file_path, 15);
    } catch (Throwable $exception) {
        $error_message = $exception->getMessage();
    }
}

$accuracy = is_numeric($rets_sofar[1]) ? (float) $rets_sofar[1] : 0.0;
$accuracy_class = $accuracy >= 65 ? 'good' : ($accuracy >= 50 ? 'medium' : 'low');
$updated_at = file_exists($file_path) ? date('M j, Y g:i A', filemtime($file_path)) : 'Unavailable';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($ticker) ?> Market Wave Dashboard</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07111f;
            --panel: #0d1a2b;
            --panel-2: #122238;
            --border: #223752;
            --text: #eef5ff;
            --muted: #8fa4bd;
            --accent: #4ade80;
            --accent-soft: rgba(74, 222, 128, .14);
            --warning: #fbbf24;
            --danger: #fb7185;
            --shadow: 0 20px 55px rgba(0, 0, 0, .28);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 0%, rgba(38, 99, 235, .16), transparent 34rem),
                radial-gradient(circle at 100% 10%, rgba(74, 222, 128, .10), transparent 28rem),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .shell {
            width: min(1440px, calc(100% - 32px));
            margin: 0 auto;
            padding: 30px 0 48px;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 20px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--accent);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1;
            letter-spacing: -.045em;
        }

        .subtitle {
            max-width: 720px;
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(13, 26, 43, .78);
            color: var(--muted);
            white-space: nowrap;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 5px var(--accent-soft);
        }

        .control-panel,
        .metric,
        .results-panel,
        .error-panel {
            border: 1px solid var(--border);
            background: rgba(13, 26, 43, .88);
            box-shadow: var(--shadow);
        }

        .control-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: end;
            margin-bottom: 18px;
            padding: 18px;
            border-radius: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 16px;
            border: 1px solid #315071;
            border-radius: 12px;
            background: #081423;
        }

        .input-prefix {
            color: var(--accent);
            font-weight: 900;
        }

        input[type="text"] {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 1.1rem;
            font-weight: 750;
            text-transform: uppercase;
        }

        button {
            min-height: 52px;
            padding: 0 24px;
            border: 0;
            border-radius: 12px;
            background: var(--accent);
            color: #05210f;
            font: inherit;
            font-weight: 900;
            cursor: pointer;
            transition: transform .18s ease, filter .18s ease;
        }

        button:hover { transform: translateY(-1px); filter: brightness(1.05); }

        .metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .metric {
            min-height: 132px;
            padding: 18px;
            border-radius: 16px;
        }

        .metric-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 750;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .metric-value {
            margin-top: 12px;
            font-size: clamp(1.25rem, 2vw, 2rem);
            font-weight: 850;
            letter-spacing: -.035em;
            overflow-wrap: anywhere;
        }

        .metric-value.good { color: var(--accent); }
        .metric-value.medium { color: var(--warning); }
        .metric-value.low { color: var(--danger); }

        .metric-note {
            margin-top: 8px;
            color: var(--muted);
            font-size: .82rem;
        }

        .results-panel {
            overflow: hidden;
            border-radius: 18px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            background: rgba(18, 34, 56, .75);
        }

        .results-header h2 {
            margin: 0;
            font-size: 1.05rem;
        }

        .results-header span {
            color: var(--muted);
            font-size: .85rem;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 820px;
        }

        td, th {
            padding: 12px 14px !important;
            border-bottom: 1px solid rgba(34, 55, 82, .72);
            color: var(--text) !important;
            background: transparent !important;
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        tr:first-child td {
            position: sticky;
            top: 0;
            z-index: 2;
            color: #bcd0e8 !important;
            background: #122238 !important;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        td:first-child { text-align: left; }
        tr:hover td { background: rgba(74, 222, 128, .045) !important; }

        td[style*="background-color:green"] {
            color: var(--accent) !important;
            background: rgba(74, 222, 128, .10) !important;
            font-weight: 800;
        }

        td[style*="background-color:red"] {
            color: var(--danger) !important;
            background: rgba(251, 113, 133, .10) !important;
            font-weight: 800;
        }

        .error-panel {
            margin-bottom: 18px;
            padding: 18px 20px;
            border-color: rgba(251, 113, 133, .42);
            border-radius: 16px;
            color: #fecdd3;
        }

        .footer-note {
            margin: 16px 2px 0;
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.55;
        }

        @media (max-width: 920px) {
            .hero { align-items: flex-start; flex-direction: column; }
            .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 620px) {
            .shell { width: min(100% - 20px, 1440px); padding-top: 20px; }
            .control-panel { grid-template-columns: 1fr; }
            .metrics { grid-template-columns: 1fr; }
            button { width: 100%; }
            .results-header { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<main class="shell">
    <header class="hero">
        <div>
            <p class="eyebrow">CNGN Market Intelligence</p>
            <h1><?= htmlspecialchars($ticker) ?> Wave Analysis</h1>
            <p class="subtitle">
                Explore the model's directional calculations, differential movement, integral values,
                and projected wave behavior in a clearer market dashboard.
            </p>
        </div>
        <div class="status-pill">
            <span class="status-dot"></span>
            Updated <?= htmlspecialchars($updated_at) ?>
        </div>
    </header>

    <form class="control-panel" method="get" action="">
        <div class="field">
            <label for="symbol">Stock ticker</label>
            <div class="input-wrap">
                <span class="input-prefix">$</span>
                <input id="symbol" name="symbol" type="text" value="<?= htmlspecialchars($ticker) ?>" maxlength="12" autocomplete="off" spellcheck="false">
            </div>
        </div>
        <button type="submit">Analyze ticker</button>
    </form>

    <?php if ($error_message !== ''): ?>
        <section class="error-panel">
            <strong>Unable to load <?= htmlspecialchars($ticker) ?>.</strong>
            <?= htmlspecialchars($error_message) ?>
        </section>
    <?php endif; ?>

    <section class="metrics" aria-label="Analysis summary">
        <article class="metric">
            <div class="metric-label">Selected symbol</div>
            <div class="metric-value"><?= htmlspecialchars($ticker) ?></div>
            <div class="metric-note">Yahoo Finance daily history</div>
        </article>

        <article class="metric">
            <div class="metric-label">Directional accuracy</div>
            <div class="metric-value <?= $accuracy_class ?>"><?= number_format($accuracy, 2) ?>%</div>
            <div class="metric-note">Historical model agreement</div>
        </article>

        <article class="metric">
            <div class="metric-label">Analysis window</div>
            <div class="metric-value">15 periods</div>
            <div class="metric-note">Current calculation interval</div>
        </article>

        <article class="metric">
            <div class="metric-label">Data source</div>
            <div class="metric-value">1 year</div>
            <div class="metric-note"><?= htmlspecialchars($display_path) ?></div>
        </article>
    </section>

    <section class="results-panel">
        <div class="results-header">
            <h2>Wave Calculation Detail</h2>
            <span>Scroll horizontally to view every calculated field</span>
        </div>
        <div class="table-scroll">
            <table>
                <?= $rets_sofar[0] ?>
            </table>
        </div>
    </section>

    <p class="footer-note">
        This interface presents experimental model output and is not financial advice.
        Data availability depends on the upstream market-data service and server configuration.
    </p>
</main>
</body>
</html>
