<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_searchjse
 */
 
defined('_JEXEC') or die;

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
			<div class="cat-desc">
			<?php echo $item->text; ?>
			</div>
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