<?php
/* SVN FILE: $Id: inflector.php 5421 2007-07-09 04:58:57Z phpnut $ */
/**
 * Pluralize and singularize English words.
 *
 * Used by Cake's naming conventions throughout the framework.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 5421 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-07-08 23:58:57 -0500 (Sun, 08 Jul 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Included libraries.
 *
 */
	if (!class_exists('Object')) {
		 uses('object');
	}
	uses('Set');
/**
 * Pluralize and singularize English words.
 *
 * Inflector pluralizes and singularizes English nouns.
 * Used by Cake's naming conventions throughout the framework.
 * Test with $i = new Inflector(); $i->test();
 *
 * @package		cake
 * @subpackage	cake.cake.libs
 */
class Inflector {
/**
 * Constructor.
 *
 */
	function __construct() {
		parent::__construct();
	}
/**
 * Gets a reference to the Inflector object instance
 *
 * @return object
 * @access public
 */
	function &getInstance() {
		static $instance = array();

		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new Inflector();
		}

		return $instance[0];
	}
/**
 * Initializes plural inflection rules
 *
 * @access protected
 */
	function __initPluralRules() {
		$_this =& Inflector::getInstance();
		$corePluralRules = array('/(s)tatus$/i' => '\1\2tatuses',
									'/(quiz)$/i' => '\1zes',
									'/^(ox)$/i' => '\1\2en', # ox
									'/([m|l])ouse$/i' => '\1ice', # mouse, louse
									'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices', # matrix, vertex, index
									'/(x|ch|ss|sh)$/i' => '\1es', # search, switch, fix, box, process, address
									'/([^aeiouy]|qu)y$/i' => '\1ies', # query, ability, agency
									'/(hive)$/i' => '\1s', # archive, hive
									'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves', # half, safe, wife
									'/sis$/i' => 'ses', # basis, diagnosis
									'/([ti])um$/i' => '\1a', # datum, medium
									'/(p)erson$/i' => '\1eople', # person, salesperson
									'/(m)an$/i' => '\1en', # man, woman, spokesman
									'/(c)hild$/i' => '\1hildren', # child
									'/(buffal|tomat)o$/i' => '\1\2oes', # buffalo, tomato
									'/us$/' => 'uses', # us
									'/(alias)$/i' => '\1es', # alias
									'/(octop|vir)us$/i' => '\1i', # octopus, virus - virus has no defined plural (according to Latin/dictionary.com), but viri is better than viruses/viruss
									'/(ax|cri|test)is$/i' => '\1es', # axis, crisis
									'/s$/' => 's',  # no change (compatibility)
									'/$/' => 's',);

		$coreUninflectedPlural = array('.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'Amoyese',
											'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
											'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
											'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
											'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
											'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
											'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
											'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
											'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
											'whiting', 'wildebeest', 'Yengeese',);

		$coreIrregularPlural = array('atlas' => 'atlases',
										'beef' => 'beefs',
										'brother' => 'brothers',
										'child' => 'children',
										'corpus' => 'corpuses',
										'cow' => 'cows',
										'ganglion' => 'ganglions',
										'genie' => 'genies',
										'genus' => 'genera',
										'graffito' => 'graffiti',
										'hoof' => 'hoofs',
										'loaf' => 'loaves',
										'man' => 'men',
										'money' => 'monies',
										'mongoose' => 'mongooses',
										'move' => 'moves',
										'mythos' => 'mythoi',
										'numen' => 'numina',
										'occiput' => 'occiputs',
										'octopus' => 'octopuses',
										'opus' => 'opuses',
										'ox' => 'oxen',
										'penis' => 'penises',
										'person' => 'people',
										'sex' => 'sexes',
										'soliloquy' => 'soliloquies',
										'testis' => 'testes',
										'trilby' => 'trilbys',
										'turf' => 'turfs',);

		$pluralRules = $corePluralRules;
		$uninflected = $coreUninflectedPlural;
		$irregular = $coreIrregularPlural;

		if (file_exists(CONFIGS . 'inflections.php')) {
			include(CONFIGS.'inflections.php');
			$pluralRules = Set::pushDiff($pluralRules, $corePluralRules);
			$uninflected = Set::pushDiff($uninflectedPlural, $coreUninflectedPlural);
			$irregular = Set::pushDiff($irregularPlural, $coreIrregularPlural);
		}
		$_this->pluralRules = array('pluralRules' => $pluralRules, 'uninflected' => $uninflected, 'irregular' => $irregular);
		$_this->pluralized = array();
	}
/**
 * Return $word in plural form.
 *
 * @param string $word Word in singular
 * @return string Word in plural
 * @access public
 * @static
 */
	function pluralize($word) {

		$_this =& Inflector::getInstance();
		if (!isset($_this->pluralRules) || empty($_this->pluralRules)) {
			$_this->__initPluralRules();
		}

		if (isset($_this->pluralized[$word])) {
			return $_this->pluralized[$word];
		}

		extract($_this->pluralRules);
		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = __enclose(join( '|', $uninflected));
			$regexIrregular = __enclose(join( '|', array_keys($irregular)));
			$_this->pluralRules['regexUninflected'] = $regexUninflected;
			$_this->pluralRules['regexIrregular'] = $regexIrregular;
		}

		if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
			$_this->pluralized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return $_this->pluralized[$word];
		}

		if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
			$_this->pluralized[$word] = $word;
			return $word;
		}

		foreach ($pluralRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				$_this->pluralized[$word] = preg_replace($rule, $replacement, $word);
				return $_this->pluralized[$word];
			}
		}
		$_this->pluralized[$word] = $word;
		return $word;
	}
/**
 * Initializes singular inflection rules
 *
 * @access protected
 */
	function __initSingularRules() {

		$_this =& Inflector::getInstance();
		$coreSingularRules = array('/(s)tatuses$/i' => '\1\2tatus',
									'/^(.*)(menu)s$/i' => '\1\2',
									'/(quiz)zes$/i' => '\\1',
									'/(matr)ices$/i' => '\1ix',
									'/(vert|ind)ices$/i' => '\1ex',
									'/^(ox)en/i' => '\1',
									'/(alias)(es)*$/i' => '\1',
									'/([octop|vir])i$/i' => '\1us',
									'/(cris|ax|test)es$/i' => '\1is',
									'/(shoe)s$/i' => '\1',
									'/(o)es$/i' => '\1',
									'/ouses$/' => 'ouse',
									'/uses$/' => 'us',
									'/([m|l])ice$/i' => '\1ouse',
									'/(x|ch|ss|sh)es$/i' => '\1',
									'/(m)ovies$/i' => '\1\2ovie',
									'/(s)eries$/i' => '\1\2eries',
									'/([^aeiouy]|qu)ies$/i' => '\1y',
									'/([lr])ves$/i' => '\1f',
									'/(tive)s$/i' => '\1',
									'/(hive)s$/i' => '\1',
									'/(drive)s$/i' => '\1',
									'/([^f])ves$/i' => '\1fe',
									'/(^analy)ses$/i' => '\1sis',
									'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
									'/([ti])a$/i' => '\1um',
									'/(p)eople$/i' => '\1\2erson',
									'/(m)en$/i' => '\1an',
									'/(c)hildren$/i' => '\1\2hild',
									'/(n)ews$/i' => '\1\2ews',
									'/^(.*us)$/' => '\\1',
									'/s$/i' => '');

		$coreUninflectedSingular = array('.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss', 'Amoyese',
											'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers',
											'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk',
											'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
											'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese',
											'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media', 'mews', 'moose', 'mumps', 'Nankingese', 'news',
											'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings',
											'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears',
											'siemens', 'species', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
											'whiting', 'wildebeest', 'Yengeese',);

		$coreIrregularSingular = array('atlases' => 'atlas',
										'beefs' => 'beef',
										'brothers' => 'brother',
										'children' => 'child',
										'corpuses' => 'corpus',
										'cows' => 'cow',
										'ganglions' => 'ganglion',
										'genies' => 'genie',
										'genera' => 'genus',
										'graffiti' => 'graffito',
										'hoofs' => 'hoof',
										'loaves' => 'loaf',
										'men' => 'man',
										'monies' => 'money',
										'mongooses' => 'mongoose',
										'moves' => 'move',
										'mythoi' => 'mythos',
										'numina' => 'numen',
										'occiputs' => 'occiput',
										'octopuses' => 'octopus',
										'opuses' => 'opus',
										'oxen' => 'ox',
										'penises' => 'penis',
										'people' => 'person',
										'sexes' => 'sex',
										'soliloquies' => 'soliloquy',
										'testes' => 'testis',
										'trilbys' => 'trilby',
										'turfs' => 'turf',);

		$singularRules = $coreSingularRules;
		$uninflected = $coreUninflectedSingular;
		$irregular = $coreIrregularSingular;

		if (file_exists(CONFIGS . 'inflections.php')) {
			include(CONFIGS.'inflections.php');
			$singularRules = Set::pushDiff($singularRules, $coreSingularRules);
			$uninflected = Set::pushDiff($uninflectedSingular, $coreUninflectedSingular);
			$irregular = Set::pushDiff($irregularSingular, $coreIrregularSingular);
		}
		$_this->singularRules = array('singularRules' => $singularRules, 'uninflected' => $uninflected, 'irregular' => $irregular);
		$_this->singularized = array();
	}
/**
 * Return $word in singular form.
 *
 * @param string $word Word in plural
 * @return string Word in singular
 * @access public
 * @static
 */
	function singularize($word) {
		$_this =& Inflector::getInstance();
		if (!isset($_this->singularRules) || empty($_this->singularRules)) {
			$_this->__initSingularRules();
		}

		if (isset($_this->singularized[$word])) {
			return $_this->singularized[$word];
		}

		extract($_this->singularRules);
		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = __enclose(join( '|', $uninflected));
			$regexIrregular = __enclose(join( '|', array_keys($irregular)));
			$_this->singularRules['regexUninflected'] = $regexUninflected;
			$_this->singularRules['regexIrregular'] = $regexIrregular;
		}

		if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
			$_this->singularized[$word] = $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return $_this->singularized[$word];
		}

		if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
			$_this->singularized[$word] = $word;
			return $word;
		}

		foreach ($singularRules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				$_this->singularized[$word] = preg_replace($rule, $replacement, $word);
				return $_this->singularized[$word];
			}
		}
		$_this->singularized[$word] = $word;
		return $word;
	}
/**
 * Returns given $lower_case_and_underscored_word as a camelCased word.
 *
 * @param string $lower_case_and_underscored_word Word to camelize
 * @return string Camelized word. likeThis.
 * @access public
 * @static
 */
	function camelize($lowerCaseAndUnderscoredWord) {
		$replace = str_replace(" ", "", ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord)));
		return $replace;
	}
/**
 * Returns an underscore-syntaxed ($like_this_dear_reader) version of the $camel_cased_word.
 *
 * @param string $camel_cased_word Camel-cased word to be "underscorized"
 * @return string Underscore-syntaxed version of the $camel_cased_word
 * @access public
 * @static
 */
	function underscore($camelCasedWord) {
		$replace = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
		return $replace;
	}
/**
 * Returns a human-readable string from $lower_case_and_underscored_word,
 * by replacing underscores with a space, and by upper-casing the initial characters.
 *
 * @param string $lower_case_and_underscored_word String to be made more readable
 * @return string Human-readable string
 * @access public
 * @static
 */
	function humanize($lowerCaseAndUnderscoredWord) {
		$replace = ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord));
		return $replace;
	}
/**
 * Returns corresponding table name for given $class_name. ("posts" for the model class "Post").
 *
 * @param string $class_name Name of class to get database table name for
 * @return string Name of the database table for given class
 * @access public
 * @static
 */
	function tableize($className) {
		$replace = Inflector::pluralize(Inflector::underscore($className));
		return $replace;
	}
/**
 * Returns Cake model class name ("Post" for the database table "posts".) for given database table.
 *
 * @param string $tableName Name of database table to get class name for
 * @return string
 * @access public
 * @static
 */
	function classify($tableName) {
		$replace = Inflector::camelize(Inflector::singularize($tableName));
		return $replace;
	}
/**
 * Returns camelBacked version of a string.
 *
 * @param string $string
 * @return string
 * @access public
 * @static
 */
	function variable($string) {
		$string = Inflector::camelize(Inflector::underscore($string));
		$replace = strtolower(substr($string, 0, 1));
		$variable = preg_replace('/\\w/', $replace, $string, 1);
		return $variable;
	}
}

/**
 * Enclose a string for preg matching.
 *
 * @param string $string String to enclose
 * @return string Enclosed string
 */
	function __enclose($string) {
		return '(?:' . $string . ')';
	}
?>