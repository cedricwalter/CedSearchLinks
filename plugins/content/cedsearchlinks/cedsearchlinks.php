<?php
/**
 * @package     CedSearchLinks
 * @subpackage  com_cedsearchlinks
 *
 * @copyright   Copyright (C) 2013-2016 galaxiis.com All rights reserved.
 * @license     The author and holder of the copyright of the software is Cédric Walter. The licensor and as such issuer of the license and bearer of the
 *              worldwide exclusive usage rights including the rights to reproduce, distribute and make the software available to the public
 *              in any form is Galaxiis.com
 *              see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

require_once(dirname(__FILE__) . '/parser.php');

class plgContentCedSearchLinks extends JPlugin
{
	var $parser = null;
	var $photoFeedHtml;

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		//Do not run in admin area and non HTML  (rss, json, error)
		$app = JFactory::getApplication();
		if ($app->isAdmin() || JFactory::getDocument()->getType() !== 'html')
		{
			return true;
		}

		//simple performance check to determine whether bot should process further
		if (!plgContentCedSearchLinksParser::active($row->text))
		{
			return true;
		}

		// Return if we don't have a valid article id
		if (!isset($row->id) || !(int) $row->id)
		{
			return true;
		}

		$row->text = $this->replaceText($row->id, $row->text);

		return true;
	}

	/**
	 * @param $id
	 * @param $text
	 * @return mixed
	 */
	private function replaceText($id, $text)
	{
		$models = plgContentCedSearchLinksParser::parse($text);
		if ($models)
		{
			$ordering    = $this->params->get('ordering', 'newest');
			$search      = $this->params->get('search', 'com_finder');
			$target      = $this->params->get('target', 'new');
			$areas       = $this->params->get('areas');
			$i           = 0;
			$area_values = "";
			foreach ($areas as $area)
			{
				$area_values .= "&areas[$i]=$area";
				$i++;
			}

			foreach ($models as $model)
			{
				$replaced = $this->getReplacement($id, $search, $model, $ordering, $area_values, $target);
				$text     = str_replace($model->match, $replaced, $text);
			}
		}

		return $text;
	}


	/**
	 * @param $id
	 * @param $search
	 * @param $model
	 * @param $ordering
	 * @param $area_values
	 *
	 * @param $target
	 * @return string
	 */
	private function getReplacement($id, $search, $model, $ordering, $area_values, $target)
	{
		switch ($search)
		{
			case "com_finder":
				$q = "q=$model->word&option=com_finder";

				return "<a href=\"" . JUri::root(true) . "/index.php?$q\" target=\"$target\">$model->word</a>";
			case "com_search":
				$q = "searchword=$model->word&ordering=" . $ordering . "&searchphrase=exact" . $area_values . "&option=com_search";

				return "<a href=\"" . JUri::root(true) . "/index.php?$q\" target=\"$target\">$model->word</a>";



			case "remove" :
				return $model->word;
		}
		return "";
	}

}
