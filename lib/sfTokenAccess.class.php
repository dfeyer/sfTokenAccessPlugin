<?php
 /**
  * (c) 2006-2010 Dominique Feyer <dominique.feyer@reelpeek.net>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */
 
/**
 * Resume
 *
 * This class ...
 *
 * @package    sfTokenAccess
 * @author     Dominique Feyer <dominique.feyer@reelpeek.net>
 */
class sfTokenAccess
{
  protected $REQUIRED_OPTIONS = array('lifetime', 'salt', 'length');

  /**
   * @var sfMemcacheCache $cache
   */
  protected $cache;
  protected $options;

  private static $instance;

  final private function __construct()
  {
    $this->initialize();
  }

  protected function initialize()
  {
    $this->options = sfConfig::get('app_sfTokenAccess_param');

    foreach ($this->REQUIRED_OPTIONS as $option)
    {
      if (!isset($this->options[$option]) || $this->options[$option] == '' )
      {
        throw new sfInitializationException();
      }
    }

    $this->cache = new sfMemcacheCache(sfConfig::get('app_sfTokenAccess_cache'));
  }

  final public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      $c = __CLASS__;
      self::$instance = new $c;
    }

    return self::$instance;
  }

  public function generateToken()
  {
    $token = '';

    for($i = 0 ; $i < $this->options['length'] ; $i++)
    {
      $token .= $this->getRandomAlphanumeric();
    }

    try
    {
      $this->validateToken($token);
    } catch (sfTokenAccessException $e)
    {
      $this->cache->set($token, true, $this->options['lifetime']);
      return $token;
    }

    return $this->generateToken();
  }

  public function validateToken($token)
  {
    if ( $token == $this->cache->get($token, false) )
    {
      return $token;
    } else
    {
      throw new sfTokenAccessException('Invalid token');
    }
  }

  protected function getRandomAlphanumeric()
  {
    $subsets[0] = array('min' => 48, 'max' => 57);
    $subsets[1] = array('min' => 65, 'max' => 90);
    $subsets[2] = array('min' => 97, 'max' => 122);

    $s = rand(0, 2);
    $ascii_code = rand($subsets[$s]['min'], $subsets[$s]['max']);

    return chr($ascii_code);
  }

  final private function __clone()
  {
    throw new sfTokenAccessException("An instance of ".get_called_class()." cannot be cloned.");
  }
}
