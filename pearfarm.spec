<?php

require 'lib/Authy/Api.php';

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('Authy')
             ->setChannel('authy.github.com/pear')
             ->setSummary('A PHP client for Authy')
             ->setDescription('A PHP client for Authy')
             ->setReleaseVersion(Authy_Api::VERSION)
             ->setReleaseStability('stable')
             ->setApiVersion(Authy_Api::VERSION)
             ->setApiStability('stable')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'David Cuadrado', 'dcu', 'david@authy.com')
             ->addGitFiles()
             ;
