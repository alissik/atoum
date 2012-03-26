<?php

namespace mageekguy\atoum;

use
	mageekguy\atoum,
	mageekguy\atoum\asserter,
	mageekguy\atoum\exceptions
;

abstract class asserter
{
	protected $generator = null;

	public function __construct(asserter\generator $generator)
	{
		$this->generator = $generator;
	}

	public function __get($asserter)
	{
		return $this->generator->__get($asserter);
	}

	public function __call($method, $arguments)
	{
		switch ($method)
		{
			case 'foreach':
				if (isset($arguments[0]) === false || (is_array($arguments[0]) === false && $arguments[0] instanceof \traversable === false))
				{
					throw new exceptions\logic\invalidArgument('First argument of ' . get_class($this) . '::' . $method . '() must be an array or a \traversable instance');
				}
				else if (isset($arguments[1]) === false || $arguments[1] instanceof \closure === false)
				{
					throw new exceptions\logic\invalidArgument('Second argument of ' . get_class($this) . '::' . $method . '() must be a closure');
				}

				foreach ($arguments[0] as $key => $value)
				{
					call_user_func_array($arguments[1], array($this, $value, $key));
				}

				return $this;

			default:
				return $this->generator->__call($method, $arguments);
		}
	}

	public function reset()
	{
		return $this;
	}

	public function getScore()
	{
		return $this->generator->getScore();
	}

	public function getLocale()
	{
		return $this->generator->getLocale();
	}

	public function getGenerator()
	{
		return $this->generator;
	}

	public function getTypeOf($mixed)
	{
		switch (true)
		{
			case is_bool($mixed):
				return sprintf($this->getLocale()->_('boolean(%s)'), ($mixed == false ? $this->getLocale()->_('false') : $this->getLocale()->_('true')));

			case is_integer($mixed):
				return sprintf($this->getLocale()->_('integer(%s)'), $mixed);

			case is_float($mixed):
				return sprintf($this->getLocale()->_('float(%s)'), $mixed);

			case is_null($mixed):
				return 'null';

			case is_object($mixed):
				return sprintf($this->getLocale()->_('object(%s)'), get_class($mixed));

			case is_resource($mixed):
				return sprintf($this->getLocale()->_('resource(%s)'), $mixed);

			case is_string($mixed):
				return sprintf($this->getLocale()->_('string(%s) \'%s\''), strlen($mixed), $mixed);

			case is_array($mixed):
				return sprintf($this->getLocale()->_('array(%s)'), sizeof($mixed));
		}
	}

	public function must(\closure $closure)
	{
		$closure($this);

		return $this;
	}

	public abstract function setWith($mixed);

	protected function pass()
	{
		$test = $this->generator->getTest();

		if ($test !== null)
		{
			$test->getScore()->addPass();
		}

		return $this;
	}

	protected function fail($reason)
	{
		$failId = null;

		$test = $this->generator->getTest();

		if ($test !== null)
		{
			$file = $test->getPath();
			$line = null;
			$class = $test->getClass();
			$function = null;
			$method = $test->getCurrentMethod();

			foreach (array_filter(debug_backtrace(), function($backtrace) use ($file) { return isset($backtrace['file']) === true && $backtrace['file'] === $file; }) as $backtrace)
			{
				if ($line === null && isset($backtrace['line']) === true)
				{
					$line = $backtrace['line'];
				}

				if ($function === null && isset($backtrace['object']) === true && isset($backtrace['function']) === true && $backtrace['object'] === $this && $backtrace['function'] !== '__call')
				{
					$function = $backtrace['function'];
				}
			}

			$failId = $test->getScore()->addFail($file, $line, $class, $method, get_class($this) . ($function ? '::' . $function : '') . '()', $reason);
		}

		throw new asserter\exception($reason, $failId);
	}
}

?>
