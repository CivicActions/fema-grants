<?php

namespace Drupal\Component\Utility;

/**
 * Provides helper methods for manipulating numbers.
 *
 * @ingroup utility
 */
class Number {

  /**
   * Verifies that a number is a multiple of a given step.
   *
   * The implementation assumes it is dealing with IEEE 754 double precision
   * floating point numbers that are used by PHP on most systems.
   *
   * This is based on the number/range verification methods of webkit.
   *
   * Besides integers and floating numbers, we also support decimal numbers
   * which are not stored in IEEE 754 format. In somewhat higher precisions for
   * these numbers, the $step value cannot accurately represent the desired
   * precision, when it is passed as a float. Passing it as a string bypasses
   * this loss of precision and enables a correct calculation of the step
   * validity.
   *
   * @param float|string $value
   *   The value that needs to be checked.
   * @param float|string $step
   *   The step scale factor. Must be positive.
   * @param float $offset
   *   (optional) An offset, to which the difference must be a multiple of the
   *   given step.
   *
   * @return bool
   *   TRUE if no step mismatch has occurred, or FALSE otherwise.
   *
   * @see http://opensource.apple.com/source/WebCore/WebCore-1298/html/NumberInputType.cpp
   */
  public static function validStep($value, $step, $offset = 0.0) {
    $double_value = (double) abs($value - $offset);

    // If step is in scientific notation, convert it to decimal.
    $step_expanded = rtrim(number_format($step, 13, '.', ''), '0');
    // Desired precision of comparison is one order of magnitude greater than
    // the precision of the step.
    $desired_precision = strrpos($step_expanded, '.') !== FALSE ? strlen($step_expanded) - strrpos($step_expanded, '.') : 1;

    // If value is of higher precision than desired it isn't divisible by step.
    $value_precision = strrpos($double_value, '.') !== FALSE ? strlen($double_value) - strrpos($double_value, '.') - 1 : 0;
    if ($value_precision > $desired_precision) {
      return FALSE;
    }

    $double_value = (double) round($double_value, $desired_precision);

    // The fractional part of a double has 53 bits. The greatest number that
    // could be represented with that is 2^53. If the given value is even bigger
    // than $step * 2^53, then dividing by $step will result in a very small
    // remainder. Since that remainder can't even be represented with a single
    // precision float the following computation of the remainder makes no sense
    // and we can safely ignore it instead.
    if ($double_value / pow(2.0, 53) > $step) {
      return TRUE;
    }

    $expected = (double) round($step * round($double_value / $step), $desired_precision);
    // Now compute that remainder of a division by $step.
    $remainder = (double) abs($double_value - $expected);

    // $remainder is a double precision floating point number. Remainders that
    // can't be represented with single precision floats are acceptable. The
    // fractional part of a float has 24 bits. That means remainders smaller than
    // $step * 2^-24 are acceptable.
    $computed_acceptable_error = (double) ($step / pow(2.0, 24));

    return $computed_acceptable_error >= $remainder || $remainder >= ($step - $computed_acceptable_error);
  }

  /**
   * Generates a sorting code from an integer.
   *
   * Consists of a leading character indicating length, followed by N digits
   * with a numerical value in base 36 (alphadecimal). These codes can be sorted
   * as strings without altering numerical order.
   *
   * It goes:
   * 00, 01, 02, ..., 0y, 0z,
   * 110, 111, ... , 1zy, 1zz,
   * 2100, 2101, ..., 2zzy, 2zzz,
   * 31000, 31001, ...
   *
   * @param int $i
   *   The integer value to convert.
   *
   * @return string
   *   The alpha decimal value.
   *
   * @see \Drupal\Component\Utility\Number::alphadecimalToInt
   */
  public static function intToAlphadecimal($i = 0) {
    $num = base_convert((int) $i, 10, 36);
    $length = strlen($num);

    return chr($length + ord('0') - 1) . $num;
  }

  /**
   * Decodes a sorting code back to an integer.
   *
   * @param string $string
   *   The alpha decimal value to convert
   *
   * @return int
   *   The integer value.
   *
   * @see \Drupal\Component\Utility\Number::intToAlphadecimal
   */
  public static function alphadecimalToInt($string = '00') {
    return (int) base_convert(substr($string, 1), 36, 10);
  }

}
