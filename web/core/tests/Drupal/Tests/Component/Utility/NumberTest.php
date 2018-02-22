<?php

namespace Drupal\Tests\Component\Utility;

use Drupal\Component\Utility\Number;
use PHPUnit\Framework\TestCase;

/**
 * Tests number manipulation utilities.
 *
 * @group Utility
 *
 * @coversDefaultClass \Drupal\Component\Utility\Number
 *
 * @see \Drupal\Component\Utility\Number
 */
class NumberTest extends TestCase {

  /**
   * Tests Number::validStep() without offset.
   *
   * @dataProvider providerTestValidStep
   * @covers ::validStep
   *
   * @param numeric $value
   *   The value argument for Number::validStep().
   * @param numeric $step
   *   The step argument for Number::validStep().
   * @param bool $expected
   *   Expected return value from Number::validStep().
   */
  public function testValidStep($value, $step, $expected) {
    $return = Number::validStep($value, $step);
    $this->assertEquals($expected, $return);
  }

  /**
   * Tests Number::validStep() with offset.
   *
   * @dataProvider providerTestValidStepOffset
   * @covers ::validStep
   *
   * @param numeric $value
   *   The value argument for Number::validStep().
   * @param numeric $step
   *   The step argument for Number::validStep().
   * @param numeric $offset
   *   The offset argument for Number::validStep().
   * @param bool $expected
   *   Expected return value from Number::validStep().
   */
  public function testValidStepOffset($value, $step, $offset, $expected) {
    $return = Number::validStep($value, $step, $offset);
    $this->assertEquals($expected, $return);
  }

  /**
   * Provides data for self::testNumberStep().
   *
   * @see \Drupal\Tests\Component\Utility\Number::testValidStep
   */
  public static function providerTestValidStep() {
    return [
      // Value and step equal.
      [10.3, 10.3, TRUE],

      // Valid integer steps.
      [42, 21, TRUE],
      [42, 3, TRUE],

      // Valid float steps.
      [42, 10.5, TRUE],
      [1, 1 / 3, TRUE],
      [-100, 100 / 7, TRUE],
      [1000, -10, TRUE],

      // Valid and very small float steps.
      [1936.5, 3e-8, TRUE],
      [1000.12345, 1e-10, TRUE],
      [3.9999999999999, 1e-13, TRUE],

      // Invalid integer steps.
      [100, 30, FALSE],
      [-10, 4, FALSE],

      // Invalid float steps.
      [6, 5 / 7, FALSE],
      [10.3, 10.25, FALSE],

      // Step mismatches very close to being valid.
      [70 + 9e-7, 10 + 9e-7, FALSE],

      // These floats are valid, but might trigger FP math idiosyncrasies.
      // @see https://www.drupal.org/node/2230909
      [3333333, 0.01, TRUE],
      [4444444, 0.01, TRUE],
      [9990009888.96, 0.01, TRUE],
      [9990009888.99, 0.01, TRUE],
      [990088999.0096, 0.0001, TRUE],
      [990088999.0099, 0.0001, TRUE],
      [4031239412.52, 0.01, TRUE],
      [90000000000.00, 0.01, TRUE],
      [9999999999.99, 0.01, TRUE],
      [-3.1933172, 0.0000001, TRUE],
      [1109.87, 0.01, TRUE],
      [70000000, 0.01, TRUE],
      [70000000.00, 0.01, TRUE],
      [13517282.20, 0.01, TRUE],
      [13517282.21, 0.01, TRUE],

      // Precision of the value is higher than that of the step.
      [990088999.0099, 0.001, FALSE],
      [990088999.0099, 0.01, FALSE],
    ];
  }

  /**
   * Data provider for \Drupal\Test\Component\Utility\NumberTest::testValidStepOffset().
   *
   * @see \Drupal\Test\Component\Utility\NumberTest::testValidStepOffset()
   */
  public static function providerTestValidStepOffset() {
    return [
      // Try obvious fits.
      [11.3, 10.3, 1, TRUE],
      [100, 10, 50, TRUE],
      [-100, 90 / 7, -10, TRUE],
      [2 / 7 + 5 / 9, 1 / 7, 5 / 9, TRUE],

      // Ensure a small offset is still invalid.
      [10.3, 10.3, 0.0001, FALSE],
      [1 / 5, 1 / 7, 1 / 11, FALSE],

      // Try negative values and offsets.
      [1000, 10, -5, FALSE],
      [-10, 4, 0, FALSE],
      [-10, 4, -4, FALSE],
    ];
  }

  /**
   * Tests the alphadecimal conversion functions.
   *
   * @dataProvider providerTestConversions
   * @covers ::intToAlphadecimal
   * @covers ::alphadecimalToInt
   *
   * @param int $value
   *   The integer value.
   * @param string $expected
   *   The expected alphadecimal value.
   */
  public function testConversions($value, $expected) {
    $this->assertSame(Number::intToAlphadecimal($value), $expected);
    $this->assertSame($value, Number::alphadecimalToInt($expected));
  }

  /**
   * Data provider for testConversions().
   *
   * @see testConversions()
   *
   * @return array
   *   An array containing:
   *     - The integer value.
   *     - The alphadecimal value.
   */
  public function providerTestConversions() {
    return [
      [0, '00'],
      [1, '01'],
      [10, '0a'],
      [20, '0k'],
      [35, '0z'],
      [36, '110'],
      [100, '12s'],
    ];
  }

}
