<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 */

defined('_JEXEC') or die;

class SearchjseModelResults extends JModelList
{
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();
		$app = JFactory::getApplication();
			
		$nullDate = $db->getNullDate();
		$date = JFactory::getDate();
		$now = $date->toSql();
		
		$text = $app->input->get('search', '', 'string');
		
		$phrase = 'all';
		$ordering = '';
		
		$searchText = $text;
		
		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.introtext LIKE ' . $text;
				$wheres2[] = 'a.fulltext LIKE ' . $text;
				$wheres2[] = 'a.metakey LIKE ' . $text;
				$wheres2[] = 'a.metadesc LIKE ' . $text;
				$wheres2[] = 'c.description LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'LOWER(a.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.introtext) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.fulltext) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.metakey) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(a.metadesc) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(c.description) LIKE LOWER(' . $word . ')';
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}
		
		// SQLSRV changes.
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('a.alias', '!=', '0');
		$case_when .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $a_id . ' END as slug';

		$case_when1 = ' CASE WHEN ';
		$case_when1 .= $query->charLength('c.alias', '!=', '0');
		$case_when1 .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when1 .= ' ELSE ';
		$case_when1 .= $c_id . ' END as catslug';

		$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created, a.language, a.catid')
			->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text')
			->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', ' . '\'2\' AS browsernav')

			->from('#__content AS a')
			->join('INNER', '#__categories AS c ON c.id=a.catid')
			->where(
				'(' . $where . ') AND a.state=1 AND c.published = 1 AND a.access IN (' . $groups . ') '
					. 'AND c.access IN (' . $groups . ') '
					. 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
					. 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= ' . $db->quote($now) . ')'
			)
			->group('a.id, a.title, a.metadesc, a.metakey, a.created, a.introtext, a.fulltext, c.title, a.alias, c.alias, c.id')
			->order($order);
			
			
		if($catid = $this->getState('filter.catid') ) {
			$query->where('a.catid = '. $catid);
		}
		
		// Filter by language.
		if ($app->isSite() && JLanguageMultilang::isEnabled())
		{
			$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
				->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
		}
		
		return $query;
		
	}
	
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.catid'));

		return parent::getStoreId($id);
	}
	/**
	 * Get the data for a destination.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		
		require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

		$list = parent::getItems();

		if (isset($list))
		{
			foreach ($list as $key => $item)
			{
				$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language);
			}
		}


		return $list;
	}
	
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		//$this->setState('list.limit', $value);
		$this->setState('list.limit', 10);
		
		$value = $app->input->get('catid', 0, 'uint');
		$this->setState('filter.catid', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
		
		$orderCol = $app->input->get('filter_order', 'a.ordering');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content')))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		// Process show_noauth parameter
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}

		$this->setState('layout', $app->input->getString('layout'));
	}
}
