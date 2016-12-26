<?php


use JSiefer\ClassMocker\ClassMocker;
use JSiefer\MageMock\MagentoMock;



$magentoFramework = new MagentoMock();

$classMocker = new ClassMocker();
// optional cache dir for generated classes
//$classMocker->setGenerationDir('./var/generation');
$classMocker->mockFramework($magentoFramework);
$classMocker->mock('Zend_*', true);
$classMocker->enable();
