
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=us-ascii">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<title>DataTables example - Server-side processing</title>
	<link rel="shortcut icon" type="image/png" href="/media/images/favicon.png">
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://www.datatables.net/rss.xml">
	<link rel="stylesheet" type="text/css" href="/media/css/site.css?_=bee396ad4e2d966f200a570e69ab51ad">
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.6/css/jquery.dataTables.css">
	<style type="text/css" class="init">

	</style>
	<script type="text/javascript" src="/media/js/site.js?_=b25563df4f9c260468871cc92a13165e"></script>
	<script type="text/javascript" src="/media/js/dynamic.php?comments-page=examples%2Fdata_sources%2Fserver_side.html" async=""></script>
	<script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="../resources/demo.js"></script>
	<script type="text/javascript" class="init">

$(document).ready(function() {
	$('#example').dataTable( {
		"processing": true,
		"serverSide": true,
		"ajax": "../server_side/scripts/server_processing.php"
	} );
} );

	</script>
</head>

<body class="wide comments example">
	<a name="top"></a>

	<div class="fw-container">
		<div class="fw-header">
			<img src="/media/images/logo-fade.png" class="logo">

			<div class="nav-master">
				<ul>
					<li class="active"><a href="/">DataTables</a></li>
					<li><a href="//editor.datatables.net">Editor</a></li>
				</ul>

				<div class="account"></div>
			</div>

			<div class="toolbar">
				<div class="toolbar_search">
					<form action="/search" id="cse-search-box">
						<input type="hidden" name="cx" value="004673356914326163298:bcgejkcchl4"> <input type="hidden" name="cof" value="FORID:9"> <input type="hidden" name="ie"
						value="UTF-8"> <input type="text" name="q" size="31"> <input type="submit" name="sa" value="Search" class="btn">
					</form><script type="text/javascript" src="//www.google.com/cse/brand?form=cse-search-box&amp;lang=en"></script>
				</div>
			</div>

			<div id="ad"></div>
		</div>

		<div class="fw-nav">
			<div class="nav-main">
				<ul><li class="sub-active sub"><a href="/examples/index">Examples</a><ul><li class=""><a href="/examples/basic_init">Basic initialisation</a></li><li class=""><a href="/examples/advanced_init">Advanced initialisation</a></li><li class=""><a href="/examples/styling">Styling</a></li><li class="active"><a href="/examples/data_sources">Data sources</a></li><li class=""><a href="/examples/api">API</a></li><li class=""><a href="/examples/ajax">Ajax</a></li><li class=""><a href="/examples/server_side">Server-side</a></li><li class=""><a href="/examples/plug-ins">Plug-ins</a></li></ul></li><li class=" sub"><a href="/manual/index">Manual</a></li><li class=" sub"><a href="/reference/index">Reference</a></li><li class=" sub"><a href="/extensions/index">Extensions</a></li><li class=" sub"><a href="/plug-ins/index">Plug-ins</a></li><li class=""><a href="/blog/index">Blog</a></li><li class=""><a href="/forums/index">Forums</a></li><li class=""><a href="/support/index">Support</a></li><li class=""><a href="/faqs/index">FAQs</a></li><li class=""><a href="/download/index">Download</a></li><li class=""><a href="/purchase/index">Purchase</a></li></ul>
			</div>

			<div class="mobile-show">
				<a><i>Show site navigation</i></a>
			</div>
		</div>

		<div class="fw-body">
			<div class="content">
				<h1 class="page_title">Server-side processing</h1>

				<div class="info">
					<p>There are many ways to get your data into DataTables, and if you are working with seriously large databases, you might want to consider using the
					server-side options that DataTables provides. With server-side processing enabled, all paging, searching, ordering actions that DataTables performs are handed
					off to a server where an SQL engine (or similar) can perform these actions on the large data set (after all, that's what the database engine is designed for!).
					As such, each draw of the table will result in a new Ajax request being made to get the required data.</p>

					<p>Server-side processing is enabled by setting the <a href="//datatables.net/reference/option/serverSide"><code class="option" title=
					"DataTables initialisation option">serverSide<span>DT</span></code></a> option to <code>true</code> and providing an Ajax data source through the <a href=
					"//datatables.net/reference/option/ajax"><code class="option" title="DataTables initialisation option">ajax<span>DT</span></code></a> option.</p>

					<p>This example shows a very simple table, matching the other examples, but in this instance using server-side processing. For further and more complex
					examples of using server-side processing, please refer to the <a href="../server_side">server-side processing</a> examples.</p>
				</div>

				<table id="example" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Name</th>
							<th>Position</th>
							<th>Office</th>
							<th>Extn.</th>
							<th>Start date</th>
							<th>Salary</th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<th>Name</th>
							<th>Position</th>
							<th>Office</th>
							<th>Extn.</th>
							<th>Start date</th>
							<th>Salary</th>
						</tr>
					</tfoot>
				</table>

				<ul class="tabs">
					<li class="active">Javascript</li>
					<li>HTML</li>
					<li>CSS</li>
					<li>Ajax</li>
					<li>Server-side script</li>
					<li class="comment-count">Comments</li>
				</ul>

				<div class="tabs">
					<div class="js">
						<p>The Javascript shown below is used to initialise the table shown in this example:</p><code class="multiline language-js">$(document).ready(function() {
	$('#example').dataTable( {
		&quot;processing&quot;: true,
		&quot;serverSide&quot;: true,
		&quot;ajax&quot;: &quot;../server_side/scripts/server_processing.php&quot;
	} );
} );</code>

						<p>In addition to the above code, the following Javascript library files are loaded for use in this example:</p>

						<ul>
							<li><a href="//code.jquery.com/jquery-1.11.1.min.js">//code.jquery.com/jquery-1.11.1.min.js</a></li>
							<li><a href="//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js">//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js</a></li>
						</ul>
					</div>

					<div class="table">
						<p>The HTML shown below is the raw HTML table element, before it has been enhanced by DataTables:</p>
					</div>

					<div class="css">
						<div>
							<p>This example uses a little bit of additional CSS beyond what is loaded from the library files (below), in order to correctly display the table. The
							additional CSS used is shown below:</p><code class="multiline language-css"></code>
						</div>

						<p>The following CSS library files are loaded for use in this example to provide the styling of the table:</p>

						<ul>
							<li><a href="//cdn.datatables.net/1.10.6/css/jquery.dataTables.css">//cdn.datatables.net/1.10.6/css/jquery.dataTables.css</a></li>
						</ul>
					</div>

					<div class="ajax">
						<p>This table loads data by Ajax. The latest data that has been loaded is shown below. This data will update automatically as any additional data is
						loaded.</p>
					</div>

					<div class="php">
						<p>The script used to perform the server-side processing for this table is shown below. Please note that this is just an example script using PHP.
						Server-side processing scripts can be written in any language, using <a href="//datatables.net/manual/server-side">the protocol described in the DataTables
						documentation</a>.</p>
					</div>

					<div class="comments">
						<div class="comments-insert"></div>
					</div>
				</div>

				<h2>Other examples</h2>

				<div class="toc">
					<div class="toc-group">
						<h3><a href="../basic_init/index.html">Basic initialisation</a></h3>
						<ul class="toc">
							<li><a href="../basic_init/zero_configuration.html">Zero configuration</a></li>
							<li><a href="../basic_init/filter_only.html">Feature enable / disable</a></li>
							<li><a href="../basic_init/table_sorting.html">Default ordering (sorting)</a></li>
							<li><a href="../basic_init/multi_col_sort.html">Multi-column ordering</a></li>
							<li><a href="../basic_init/multiple_tables.html">Multiple tables</a></li>
							<li><a href="../basic_init/hidden_columns.html">Hidden columns</a></li>
							<li><a href="../basic_init/complex_header.html">Complex headers (rowspan and colspan)</a></li>
							<li><a href="../basic_init/dom.html">DOM positioning</a></li>
							<li><a href="../basic_init/flexible_width.html">Flexible table width</a></li>
							<li><a href="../basic_init/state_save.html">State saving</a></li>
							<li><a href="../basic_init/alt_pagination.html">Alternative pagination</a></li>
							<li><a href="../basic_init/scroll_y.html">Scroll - vertical</a></li>
							<li><a href="../basic_init/scroll_x.html">Scroll - horizontal</a></li>
							<li><a href="../basic_init/scroll_xy.html">Scroll - horizontal and vertical</a></li>
							<li><a href="../basic_init/scroll_y_theme.html">Scroll - vertical with jQuery UI ThemeRoller</a></li>
							<li><a href="../basic_init/comma-decimal.html">Language - Comma decimal place</a></li>
							<li><a href="../basic_init/language.html">Language options</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../advanced_init/index.html">Advanced initialisation</a></h3>
						<ul class="toc">
							<li><a href="../advanced_init/events_live.html">DOM / jQuery events</a></li>
							<li><a href="../advanced_init/dt_events.html">DataTables events</a></li>
							<li><a href="../advanced_init/column_render.html">Column rendering</a></li>
							<li><a href="../advanced_init/length_menu.html">Page length options</a></li>
							<li><a href="../advanced_init/dom_multiple_elements.html">Multiple table control elements</a></li>
							<li><a href="../advanced_init/complex_header.html">Complex headers (rowspan / colspan)</a></li>
							<li><a href="../advanced_init/object_dom_read.html">Read HTML to data objects</a></li>
							<li><a href="../advanced_init/html5-data-attributes.html">HTML5 data-* attributes - cell data</a></li>
							<li><a href="../advanced_init/html5-data-options.html">HTML5 data-* attributes - table options</a></li>
							<li><a href="../advanced_init/language_file.html">Language file</a></li>
							<li><a href="../advanced_init/defaults.html">Setting defaults</a></li>
							<li><a href="../advanced_init/row_callback.html">Row created callback</a></li>
							<li><a href="../advanced_init/row_grouping.html">Row grouping</a></li>
							<li><a href="../advanced_init/footer_callback.html">Footer callback</a></li>
							<li><a href="../advanced_init/dom_toolbar.html">Custom toolbar elements</a></li>
							<li><a href="../advanced_init/sort_direction_control.html">Order direction sequence control</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../styling/index.html">Styling</a></h3>
						<ul class="toc">
							<li><a href="../styling/display.html">Base style</a></li>
							<li><a href="../styling/no-classes.html">Base style - no styling classes</a></li>
							<li><a href="../styling/cell-border.html">Base style - cell borders</a></li>
							<li><a href="../styling/compact.html">Base style - compact</a></li>
							<li><a href="../styling/hover.html">Base style - hover</a></li>
							<li><a href="../styling/order-column.html">Base style - order-column</a></li>
							<li><a href="../styling/row-border.html">Base style - row borders</a></li>
							<li><a href="../styling/stripe.html">Base style - stripe</a></li>
							<li><a href="../styling/bootstrap.html">Bootstrap</a></li>
							<li><a href="../styling/foundation.html">Foundation</a></li>
							<li><a href="../styling/jqueryUI.html">jQuery UI ThemeRoller</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="./index.html">Data sources</a></h3>
						<ul class="toc active">
							<li><a href="./dom.html">HTML (DOM) sourced data</a></li>
							<li><a href="./ajax.html">Ajax sourced data</a></li>
							<li><a href="./js_array.html">Javascript sourced data</a></li>
							<li class="active"><a href="./server_side.html">Server-side processing</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../api/index.html">API</a></h3>
						<ul class="toc">
							<li><a href="../api/add_row.html">Add rows</a></li>
							<li><a href="../api/multi_filter.html">Individual column searching (text inputs)</a></li>
							<li><a href="../api/multi_filter_select.html">Individual column searching (select inputs)</a></li>
							<li><a href="../api/highlight.html">Highlighting rows and columns</a></li>
							<li><a href="../api/row_details.html">Child rows (show extra / detailed information)</a></li>
							<li><a href="../api/select_row.html">Row selection (multiple rows)</a></li>
							<li><a href="../api/select_single_row.html">Row selection and deletion (single row)</a></li>
							<li><a href="../api/form.html">Form inputs</a></li>
							<li><a href="../api/counter_columns.html">Index column</a></li>
							<li><a href="../api/show_hide.html">Show / hide columns dynamically</a></li>
							<li><a href="../api/api_in_init.html">Using API in callbacks</a></li>
							<li><a href="../api/tabs_and_scrolling.html">Scrolling and jQuery UI tabs</a></li>
							<li><a href="../api/regex.html">Search API (regular expressions)</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../ajax/index.html">Ajax</a></h3>
						<ul class="toc">
							<li><a href="../ajax/simple.html">Ajax data source (arrays)</a></li>
							<li><a href="../ajax/objects.html">Ajax data source (objects)</a></li>
							<li><a href="../ajax/deep.html">Nested object data (objects)</a></li>
							<li><a href="../ajax/objects_subarrays.html">Nested object data (arrays)</a></li>
							<li><a href="../ajax/orthogonal-data.html">Orthogonal data</a></li>
							<li><a href="../ajax/null_data_source.html">Generated content for a column</a></li>
							<li><a href="../ajax/custom_data_property.html">Custom data source property</a></li>
							<li><a href="../ajax/custom_data_flat.html">Flat array data source</a></li>
							<li><a href="../ajax/defer_render.html">Deferred rendering for speed</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../server_side/index.html">Server-side</a></h3>
						<ul class="toc">
							<li><a href="../server_side/simple.html">Server-side processing</a></li>
							<li><a href="../server_side/custom_vars.html">Custom HTTP variables</a></li>
							<li><a href="../server_side/post.html">POST data</a></li>
							<li><a href="../server_side/ids.html">Automatic addition of row ID attributes</a></li>
							<li><a href="../server_side/object_data.html">Object data source</a></li>
							<li><a href="../server_side/row_details.html">Row details</a></li>
							<li><a href="../server_side/select_rows.html">Row selection</a></li>
							<li><a href="../server_side/jsonp.html">JSONP data source for remote domains</a></li>
							<li><a href="../server_side/defer_loading.html">Deferred loading of data</a></li>
							<li><a href="../server_side/pipeline.html">Pipelining data to reduce Ajax calls for paging</a></li>
						</ul>
					</div>

					<div class="toc-group">
						<h3><a href="../plug-ins/index.html">Plug-ins</a></h3>
						<ul class="toc">
							<li><a href="../plug-ins/api.html">API plug-in methods</a></li>
							<li><a href="../plug-ins/sorting_auto.html">Ordering plug-ins (with type detection)</a></li>
							<li><a href="../plug-ins/sorting_manual.html">Ordering plug-ins (no type detection)</a></li>
							<li><a href="../plug-ins/range_filtering.html">Custom filtering - range search</a></li>
							<li><a href="../plug-ins/dom_sort.html">Live DOM ordering</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="fw-footer">
			<div class="copyright">
				DataTables designed and created by <a href="//sprymedia.co.uk">SpryMedia Ltd</a> &#169; 2007-2015. <a href="/license/mit">MIT licensed</a>. Our <a href=
				"/supporters">Supporters</a><br>
				SpryMedia Ltd is registered in Scotland, company no. SC456502.
			</div>
		</div>
	</div><script type="text/javascript">
			  var _gaq = _gaq || [];
				  _gaq.push(['_setAccount', 'UA-365466-5']);
				  _gaq.push(['_trackPageview']);

				  (function() {
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				  })();
	</script>
</body>
</html>