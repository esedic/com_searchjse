<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_searchjse
 */
 
defined('_JEXEC') or die;

// $app = JFactory::getApplication();
// $catid = $app->input->get('catid');

// $db = JFactory::getDBO();
// $db->setQuery("SELECT description FROM #__categories WHERE id = ".$catid." LIMIT 1;");
// $catDesc = $db->loadResult();

// $catArray = array(94,96,100,99,102,103,106,109,147,148,92,88,51,52,53,68,93,57,49,71,74,76,83,86,87,149,89,85,84,77,153,152,151,150,114,113,105,154,16,50,91,97,98,101,104,108,110,143,144,145,90,82,80,54,55,56,58,59,69,73,75,78,79,146);

// if (in_array($catid, $catArray)) {
//     echo '<div class="category-desc">'.$catDesc.'</div>';
// }

if(count($this->items) == 0) {
	//echo "kategorija nima vsebine";	
} else {

	echo '<h2 class="category-title">'.$this->items[0]->section.'</h2><div class="triangle"></div>';
	
	foreach((array)$this->items as $item) : ?>
		<div class="article-block">
			 <h3 class="article-title">
				<a href="<?php echo $item->href;?>"><?php echo $item->title;?></a>
			</h3>
			<span class="article-date">
				<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC7')); ?>
			</span>
		</div>
		<?php 
		//echo $item->text;
		?>
	<?php endforeach;?>

	<?php if(!empty($this->pagination->getPagesLinks())) : ?>
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks();?>
		</div>
	<?php endif; ?>

<?php }?>