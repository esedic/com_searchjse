<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_searchjse
 */
 
defined('_JEXEC') or die;
$app = JFactory::getApplication();

$option = $app->input->get('option');
$view = $app->input->get('view');
$searchword = $app->input->get('search', '', 'string');

?>

<div id="loader"><p>Iskanje se izvaja...</p></div>
<h1 class="search-title">Iskanje po strani</h1>

<form name="search" action="<?php echo JUri::current();?>" method="get">
	<div class="row">
		<div class="col-sm-5">
			<div class="input-group">
				<input type="input" name="search" value="<?php echo $app->input->get('search', '', 'string');?>" class="form-control" placeholder="Vpiši iskalni izraz">
				<span class="input-group-btn">
					<button class="btn btn-primary" type="submit">Išči</button>
				</span>
			</div>
		</div>
	</div>
</form>

<?php 
	if(isset($searchword)) {
		echo '<p class="search-word">Iskana beseda: <span class="label label-primary">' . $searchword . '</span></p>';
	}
?>

<?php
foreach($this->catid as $cat) : ?>	
    <section class="category-block" data-caid="<?php echo $cat;?>"></section>
<?php endforeach ?>


<script type="text/javascript">
jQuery(document).ready(function($){

	
	var filterText = "<?php echo $app->input->get('search', '', 'string');?>";

	$( ".category-block" ).each(function( index ) {
		//$(this).load( "index.php?option=com_searchjse&view=results&catid="+$(this).attr("data-caid")+"&start=0&layout=default_"+ $(this).attr("data-caid") + "&format=raw&search="+filterText );

		$(this).load(encodeURI("<?php echo JRoute::_("index.php?option=com_searchjse&view=results");?>&catid="+$(this).attr("data-caid")+"&start=0&layout=default_"+ $(this).attr("data-caid") + "&format=raw&search="+filterText), function(response, status, xhr) {
			if ( status == "success" ) {
				$('iframe.smartify').smartify({
					src_attr: 'data-src'
				});
				if ($(this).children().length < 1) {
					//$(this).remove();
				}
				else {
					$(this).addClass('hasItems');
				    //$().prettyEmbed();
				    $('#loader').remove();
				}
			}
		});
	});



	$(document).on( "click", ".pagination li a", function( event ) {
		event.preventDefault();
		var content = $(this).closest(".category-block");
		var page = $.urlParam('start',$(this).attr("href")); 
		
		content.addClass("loading");
	
		$(this).closest(".category-block").load(encodeURI("<?php echo JRoute::_("index.php?option=com_searchjse&view=results");?>&catid="+content.attr("data-caid")+"&start="+page+"&layout=default_" + $(this).attr("data-caid") + "&format=raw&search="+filterText), function(response, status, xhr){
			if ( status == "success" ) {
				content.removeClass("loading");
			}
		});
	});	
	
	$.urlParam = function(name, url){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
		if (results==null){
		   return null;
		}
		else{
		   return results[1] || 0;
		}
	}
});
</script>
<?php
$doc = JFactory::getDocument();
$assets	= JUri::root().'components/com_searchjse/assets/';
$doc->addStyleDeclaration('
	.loading {
	    background: rgba(255,255,255,0.35);
	    background-image: url('.$assets.'ajax-squares-medium.gif);
	    background-repeat: no-repeat;
	    background-position: 50% 50%;
	}
	.loading > p {
	    opacity: 0.5;
	}
	#loader {
		background: url('.$assets.'ajax-squares-big.gif) no-repeat 50% 50% rgba(255,255,255,0.95);
		min-width: 100%;
		min-height: 100%;
		width: 50%;
		height: 50%;
		overflow: auto;
		margin: auto;
		position: fixed;
		top: 0; left: 0; bottom: 0; right: 0;
		z-index: 1000;
	}
	#loader > p {
		bottom: 0;
	    top: 57%; left: 0; bottom: 0; right: 0;
	    margin: auto;
	    position: relative;
	    text-align: center;
	    font-size: 16px;
	    font-family: "Roboto", sans-serif;
	}

');
?>
<style>

</style>