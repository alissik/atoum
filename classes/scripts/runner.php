<?php

namespace mageekguy\atoum\scripts;

use \mageekguy\atoum;
use \mageekguy\atoum\exceptions;

class runner extends atoum\script
{
	const version = '$Rev: 234 $';

	protected $runner = null;

	public function __construct($name, atoum\locale $locale = null, atoum\adapter $adapter = null)
	{
		parent::__construct($name, $locale, $adapter);

		$this->setRunner(new atoum\runner());
	}

	public function setRunner(atoum\runner $runner)
	{
		$this->runner = $runner;

		return $this;
	}

	public function getRunner()
	{
		return $this->runner;
	}

	public function run(array $arguments = array())
	{
		$runner = $this->runner;

		$this->argumentsParser->addHandler(
			function($script, $argument, $values) {
				if (sizeof($values) !== 0)
				{
					throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
				}

				$script->help();
			},
			array('-v', '--version')
		);

		$this->argumentsParser->addHandler(
			function($script, $argument, $values) {
				if (sizeof($values) !== 0)
				{
					throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
				}

				$script->help();
			},
			array('-h', '--help')
		);

		$this->argumentsParser->addHandler(
			function($script, $argument, $files) use ($runner) {
				if (sizeof($files) <= 0)
				{
					throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
				}

				foreach ($files as $file)
				{
					if (file_exists($file) === false)
					{
						throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Configuration file path \'%s\' is invalid'), $file));
					}

					if (is_readable($file) === false)
					{
						throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Unable to read configuration file \'%s\''), $file));
					}

					require_once($file);
				}
			},
			array('-c', '--configuration-files')
		);

		if (realpath($_SERVER['argv'][0]) === $this->getName())
		{
			$this->argumentsParser->addHandler(
				function($script, $argument, $files) {
					if (sizeof($files) <= 0)
					{
						throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Bad usage of %s, do php %s --help for more informations'), $argument, $script->getName()));
					}

					foreach ($files as $file)
					{
						if (file_exists($file) === false)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Test file path \'%s\' is invalid'), $file));
						}

						if (is_readable($file) === false)
						{
							throw new exceptions\logic\invalidArgument(sprintf($script->getLocale()->_('Unable to read test file \'%s\''), $file));
						}

						require_once($file);
					}
				},
				array('-t', '--test-files')
			);
		}

		if ($runner->hasReports() === false)
		{
			$report = new atoum\reports\cli();
			$report->addWriter(new atoum\writers\stdout());

			$runner->addReport($report);
		}

		parent::run($arguments);

		$runner->run();
	}

	public function version()
	{
		$this
			->writeMessage(sprintf($this->locale->_('autorunner of \mageekguy\atoum version %s'), self::version) . PHP_EOL)
		;

		return $this;
	}

	public function help()
	{
		$this
			->writeMessage(sprintf($this->locale->_('Usage: %s [options]'), $this->getName()) . PHP_EOL)
			->writeMessage($this->locale->_('Available options are:') . PHP_EOL)
		;

		$this->writeLabels(
			array(
				'-h, --help' => $this->locale->_('Display this help'),
				'-v, --version' => $this->locale->_('Display version'),
				'-c <file>+, --configuration-files <file>+' => $this->locale->_('Use configuration files')
			)
		);

		return $this;
	}
}

?>