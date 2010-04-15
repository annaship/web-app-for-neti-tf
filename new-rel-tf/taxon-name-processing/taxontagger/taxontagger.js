/*
* Developed by: Michael Giddens @ SilverBiology
*	@lastModified: Sept. 26th 2009
*/
Ext.onReady(function(){

	Ext.QuickTips.init();

	// Disable browser right click
	Ext.fly(document.body).on('contextmenu', function(e, target) {
		e.preventDefault();
	});	

	var list_grid = new Ext.grid.GridPanel({
			store: new Ext.data.GroupingStore({
					reader: new Ext.data.ArrayReader({
						id: 0
					}, [
							{name: 'offset', mapping: 'offset'}
						, {name: 'orig', mapping: 'orig'}
						, {name: 'full', mapping: 'full'}
						, {name: 'source', mapping: 'source'}
					])
				,	data: []
				,	groupField: 'full'
				, sortInfo:{field: 'full', direction: "ASC"}
			})
		,	columns: [
//					{header: "Offset", width: 80, sortable: true, dataIndex: 'offset'}
					{header: "Species Orig.", width: 120, sortable: true, dataIndex: 'orig'}
				,	{header: "Species Full", width: 120, sortable: true, dataIndex: 'full'}
				,	{header: "Found By", width: 120, sortable: true, dataIndex: 'source'}
			]
		,	view: new Ext.grid.GroupingView({
					forceFit:true
				,	groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
	    })
		,	sm: new Ext.grid.RowSelectionModel({
					singleSelect:true
				,	listeners: {
						rowselect: function( grid, i, row) {

							Ext.getCmp('vp').getEl().mask("Loading...");
							Ext.Ajax.request({
									url: 'get.php'
								,	success: function( r ) {

										r = Ext.decode(r.responseText);
										if (r.data == null) {
											return(0);
										}

										if(!Ext.getCmp('ubio:' + r.data.namebankID)) {

											var tp = new Ext.TabPanel({
													border: false
												,	activeTab: 0
												,	deferredRender: true
												,	items: [{
															store: new Ext.data.Store({
																	reader: new Ext.data.ArrayReader({
																		id: 1
																	}, [
																			{name: 'taxaJoin', mapping: 'taxaJoin'}
																		,	{name: 'source', mapping: 'source'}
																	])
																,	data: []
															})
														,	columns: [
																	{header: "Source", width: 100, sortable: true, dataIndex: 'source'}
																,	{header: "Higher Taxanomic Ranks", width: 750, sortable: true, dataIndex: 'taxaJoin'}
															]
														,	view: new Ext.grid.GridView({
																	forceFit: false
																,	emptyText: 'No higher taxa information available.'
																,	deferEmptyText: false
												//				,	getGroupState: Ext.emptyFn
															})			
														,	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	//													,	region: 'south'
										//				,	split: true
//														, height: 75
														,	autoScroll: true
														,	xtype: 'grid'
														,	border: false
														,	title: 'Higher Taxa'
													}, {
															store: {
																fields: ['nameString', 'languageName']
															,	reader: new Ext.data.JsonReader({
																		idProperty: 'namebankID'          
																	,	fields: [
																				{name: 'nameString'}
																			,	{name: 'languageName'}
																		]    
																})
															}
														,	xtype: 'grid'
														,	title: 'Vernacular (Common Names)'
														,	autoScroll: true
														,	deferEmptyText: false
														,	multiSelect: false
														,	emptyText: 'No vernaculars to display.'
														,	reserveScrollOffset: true
														,	columns: [{
																	header: 'Language'
																,	width: 100
																,	dataIndex: 'languageName'
															}, {
																	header: 'Common Name'
																,	width: 500
																,	dataIndex: 'nameString'
															}]
													}, {
															store: {
																	fields: ['fullNameString']
																,	reader: new Ext.data.JsonReader({
																			idProperty: 'namebankID'          
																		,	fields: [
																					{name: 'fullNameString'}
																			]    
																	})
															}
														,	xtype: 'grid'
														,	title: 'Synonyms'
														,	tabTip: 'Synonyms are derived from the UBIO service.'
														,	multiSelect: true
														,	emptyText: 'No synonyms to display.'
														,	deferEmptyText: false
														,	reserveScrollOffset: true
														,	columns: [{
																	header: 'Scientific Name'
																,	width: 550
																,	dataIndex: 'fullNameString'
															}]
													}, {
															store: {
																	fields: ['fullNameString']
																,	reader: new Ext.data.JsonReader({
																			idProperty: 'namebankID'          
																		,	fields: [
																					{name: 'fullNameString'}
																			]    
																	})
															}
														,	xtype: 'grid'
														,	title: 'Citations'
														,	multiSelect: false
														,	emptyText: 'No citations to display.'
														,	deferEmptyText: false
														,	reserveScrollOffset: true
														,	columns: [{
																	header: 'Name'
																,	width: 100
																,	dataIndex: 'nameString'
															}]
													}]
											});
											
											extra_tabs.add({
													title: row.data.full
												, id: 'ubio:' + r.data.namebankID
												,	closable: true
												,	layout: 'fit'
												,	items: [ tp	]
											}).show();
	
											tp.items.items[0].store.loadData( r.data.highertaxa );
											tp.items.items[1].store.loadData( r.data.vernacular );
											tp.items.items[2].store.loadData( r.data.synonyms );
											tp.items.items[3].store.loadData( r.data.citations );
										} else {
											extra_tabs.setActiveTab('ubio:' + r.data.namebankID);
										}
										Ext.getCmp('vp').getEl().unmask();
									}
								,	failure: function() {
										Ext.getCmp('vp').getEl().unmask();
									}
								,	params: { 
											cmd: 'highertaxa' 
										,	ScientificName: row.data.full
									}
							});

						}
					}
			})
		,	title:'Grid View'
	});

	var output_tabs = new Ext.TabPanel({
			activeTab: 0
		,	region: 'east'
		,	id: 'output_tabs'
		, title: 'Output List'
		, split: true
		, width: 400
		,	deferredRender: false
		,	items: [ list_grid, {
					title: 'Delimited'
				,	id: 'list-delimited'
				,	autoScroll: true
/*				
				,	bbar: new Ext.StatusBar({
            defaultText: 'Total Selected Items: 0'
          , id: 'list_delimited_statusbar'
        })
*/				
			},{
					title: 'White List'
				,	id: 'list-white'
/*				
				,	bbar: new Ext.StatusBar({
            defaultText: 'Total Selected Items: 0'
          , id: 'list_white_statusbar'
        })
*/				
			}]
	});

	var extra_tabs = new Ext.TabPanel({
			region: 'south'
		,	split: true
		,	resizeTabs: true
		,	minTabWidth: 115
		,	tabWidth: 135
		,	enableTabScroll: true
		,	height: 150
//			plugins: new Ext.ux.TabCloseMenu()
	});

	var main_panel = new Ext.Panel({
			region:'center'
		,	title: 'Taxon Tagger'
		,	id: 'center'
		,	autoScroll: true
/*		
		,	bodyCfg: {
					tag: 'iframe'
				,	id: 'center2'
				,	src: 'blank.html'
				,	style: {
							border: '0px none'
						,	'background-color': '#FFF'
					}
			}
*/			
		,	tbar: ['URL: '
			, {
					xtype: 'textfield'
				, id: 'url'
				,	value: 'http://www.bioline.org.br/abstract?id=fb95003'
				,	width: 300
			}, {
					text: 'Load'
				,	iconCls: 'icon_load'
				,	handler: function() {
					 var tmp_url = Ext.getCmp('url').getValue();										 
					 Ext.getCmp('center').load({
							url: 'get.php'
						,	params: {url: tmp_url}
						,	text: "Loading..."
						,	timeout: 60
						,	callback: mark
					});
				 }
			}, {
					text: 'Mark Scientific Name'
				,	iconCls: 'icon_mark'
				,	id: 'markScientificName'
				,	hidden: true
				,	handler: function() {
						var txt = '';
						txt = document.getSelection();
						highlightSearchTerms( txt, true );
					}
			}, {
					text: 'Toggle Full Name'
				,	iconCls: 'icon_fullname'
				,	id: 'toggleFullname'
				,	hidden: true
				,	xtype: 'button'
				, allowDepress: true
				,	enableToggle: true
				, toggleHandler: function( btn, state ) {
			
						Ext.select('name.scientific_name').each(function( el ) {
							var full = '';
							var type = '';
							Ext.each( el.dom.attributes, function( item ) {
								if (this.nodeName == 'full') full = this.nodeValue;
								if (this.nodeName == 'type') type = this.nodeValue;
							});
							if (type == 'taxonfinder') {
								this.update( full );
							}
						});
			
					}
			}, {
					text: 'Higher Taxa Grid'
//				,	iconCls: 'icon_mark'
				,	id: 'highertaxaPanel'
				,	hidden: true
				,	handler: function() {
						var htw = new Ext.Window({
								title: 'Higher Taxa Grid'
							,	width: 800
							, height: 500
							,	layout: 'fit'
							, items: [{
										store: {
												fields: ['nameString', 'languageName']
//											,	url: 'get.php'
											,	proxy: new Ext.data.HttpProxy({ url: "get.php", timeout: 60000 })
//											,	root: "106"
											,	baseParams: {
													cmd: 'taxagrid'
												}
											,	reader: new Ext.data.JsonReader({
														idProperty: 'namebankID'          
													,	fields: [
																{name: 'scientificname'}
															,	{name: 'genus'}
															,	{name: 'family'}
															,	{name: 'class'}
															,	{name: 'order'}
															,	{name: 'phylum'}
															,	{name: 'kingdom'}
														]    
												})
										}
									,	columns: [
												{header: "Scientific Name", width: 150, sortable: true, dataIndex: 'scientificname'}
											,	{header: "Genus", width: 100, sortable: true, dataIndex: 'genus'}
											,	{header: "Family", width: 100, sortable: true, dataIndex: 'family'}
											,	{header: "Class", width: 100, sortable: true, dataIndex: 'class'}
											,	{header: "Order", width: 100, sortable: true, dataIndex: 'order'}
											,	{header: "Phylum", width: 100, sortable: true, dataIndex: 'phylum'}
											,	{header: "Kingdom", width: 90, sortable: true, dataIndex: 'kingdom'}
										]
									,	view: new Ext.grid.GridView({
												forceFit: false
											,	emptyText: 'No information available.'
											,	deferEmptyText: false
										})
									,	tbar: ['Source: Species 2000 & Catalogue of Life 2008']
									,	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
									,	border: false
									,	xtype: 'grid'
									,	loadMask: true
									,	autoScroll: true
								}]
						});
						htw.show();

						var tmpName = '';
						var list = '';
						list_grid.store.data.each(function( i ) {
							if (i.data.full != tmpName) {
								if (tmpName != '')
									list += ",";
								list += "{ \"name\": \"" + i.data.full + "\"}";
								tmpName = i.data.full;
							}
						});
						list = "[" + list + "]";

						htw.items.items[0].store.load({
								params: { list: list }
						});
					}
			}]				
	});

	var viewport = new Ext.Viewport({
			layout: 'border'
		,	id: 'vp'
		,	items:[ output_tabs, main_panel, extra_tabs ]
	});

	function mark() {
		Ext.select('name.scientific_name').each(function( el ) {
			Ext.DomHelper.insertAfter( el, {
					tag: 'span'
				, cls: 'remove'
				, html: '&nbsp;&nbsp;&nbsp;'
				,	qtip: 'This removes the scientific name from the list.'
			});
		});
		generateList();
		Ext.get('center').select('span.remove').on('click', removeMarker );
		Ext.get('center').select('name.scientific_name').on('contextmenu', showContext );
		Ext.get('center').select('a').on('click', followLink );
		Ext.getCmp('markScientificName').show();
		Ext.getCmp('toggleFullname').show();
		Ext.getCmp('highertaxaPanel').show();
	}


	function followLink( e, el ) {
		e.preventDefault();
	}
	
	function showContext( e, b, c) {

		var items = [];						

		items.push({
				text:'Edit Manually'
			, iconCls: 'icon_specimen_details'
			, scope: this
			,	handler: function() { 
				}
		});

		items.push({
				text:'Taxamatch GNI'
			, iconCls: 'icon_specimen_details'
			, scope: this
			,	handler: function() { 
				}
		});

		var menu = new Ext.menu.Menu({
				items: items
//			, record: record
		});  
		var xy = e.getXY();
		menu.showAt(xy); 
					
	}
	
	function generateList() {
		var list = '';
		var whitelist = '';
		var i = 0;
		var record_list = [];						

		Ext.select('name.scientific_name').each(function( el ) {
			var full = '';
			var offset = '';
			var type = '';
			Ext.each( el.dom.attributes, function( item ) {
				if (this.nodeName == 'full') full = this.nodeValue;
				if (this.nodeName == 'offset') offset = this.nodeValue;
				if (this.nodeName == 'type') type = this.nodeValue;
			});
			whitelist += full + "<br>";
//			list += offset + "|" + full + "|" + this.dom.innerHTML + '|' + type + '<br>';
			list += full + "|" + this.dom.innerHTML + '|' + type + '<br>';
			i++;
			
			record_list.push({
					offset: offset
				, full: full
				, orig: this.dom.innerHTML
				,	source: type
			});
						
		});
		list_grid.store.loadData( record_list );
		
		Ext.getCmp('list-delimited').body.update( "<div class='list'>" + list + "<div>" );
//		Ext.getCmp('list_delimited_statusbar').setStatus('Total Marked Species: ' + i);
		Ext.getCmp('list-white').body.update( "<div class='list'>" + whitelist + "<div>" );
//		Ext.getCmp('list_white_statusbar').setStatus('Total Marked Items: ' + i);

	}
										
	// This is the function that actually highlights a text string by
	// adding HTML tags before and after all occurrences of the search
	// term. You can pass your own tags if you'd like, or if the
	// highlightStartTag or highlightEndTag parameters are omitted or
	// are empty strings then the default <font> tags will be used.
	function doHighlight(bodyText, searchTerm, highlightStartTag, highlightEndTag) 
	{
		// the highlightStartTag and highlightEndTag parameters are optional
		if ((!highlightStartTag) || (!highlightEndTag)) {
			highlightStartTag = "<name class='scientific_name manual' type='manual' full='" + searchTerm + "' offset='-1'>";
			highlightEndTag = "</name><span class='remove manual'>&nbsp;&nbsp;&nbsp;</span>";
		}
		
		// find all occurences of the search term in the given text,
		// and add some "highlight" tags to them (we're not using a
		// regular expression search, because we want to filter out
		// matches that occur within HTML tags and script blocks, so
		// we have to do a little extra validation)
		var newText = "";
		var i = -1;
		var lcSearchTerm = searchTerm.toLowerCase();
		var lcBodyText = bodyText.toLowerCase();
			
		while (bodyText.length > 0) {
			i = lcBodyText.indexOf(lcSearchTerm, i+1);
			if (i < 0) {
				newText += bodyText;
				bodyText = "";
			} else {
				// skip anything inside an HTML tag
				if (bodyText.lastIndexOf(">", i) >= bodyText.lastIndexOf("<", i)) {
					// skip anything inside a <script> block
					if (lcBodyText.lastIndexOf("/script>", i) >= lcBodyText.lastIndexOf("<script", i)) {
						newText += bodyText.substring(0, i) + highlightStartTag + bodyText.substr(i, searchTerm.length) + highlightEndTag;
						bodyText = bodyText.substr(i + searchTerm.length);
						lcBodyText = bodyText.toLowerCase();
						i = -1;
					}
				}
			}
		}
		
		return newText;
	}

	function removeMarker() {
		var o = Ext.fly( this );
		var txt = o.prev().dom.innerHTML;	

		Ext.each( Ext.get('center').select('span.remove').elements, function() {
			var o2 = Ext.fly( this );
			if (o2.prev().dom.innerHTML == txt ) {
				Ext.DomHelper.insertBefore(o2.prev(), txt);
				o2.prev().remove();
				o2.remove();
			}
		});
		
		Ext.DomHelper.insertBefore(o.prev(), txt);
		o.prev().remove();
		o.remove();
		generateList();
	}
	
	// This is sort of a wrapper function to the doHighlight function.
	// It takes the searchText that you pass, optionally splits it into
	// separate words, and transforms the text on the current web page.
	// Only the "searchText" parameter is required; all other parameters
	// are optional and can be omitted.
	function highlightSearchTerms(searchText, treatAsPhrase, warnOnFailure, highlightStartTag, highlightEndTag)
	{
		// if the treatAsPhrase parameter is true, then we should search for 
		// the entire phrase that was entered; otherwise, we will split the
		// search string so that each word is searched for and highlighted
		// individually
		if (treatAsPhrase) {
			searchArray = [searchText];
		} else {
			searchArray = searchText.split(" ");
		}
		
		if (!document.body || typeof(document.body.innerHTML) == "undefined") {
			if (warnOnFailure) {
				alert("Sorry, for some reason the text of this page is unavailable. Searching will not work.");
			}
			return false;
		}
		
		var bodyText = Ext.getCmp('center').body.dom.innerHTML;
		for (var i = 0; i < searchArray.length; i++) {
			bodyText = doHighlight(bodyText, searchArray[i], highlightStartTag, highlightEndTag);
		}
		
		Ext.getCmp('center').body.update( bodyText );
		generateList();
		Ext.get('center').select('span.remove').on('click', removeMarker );
		Ext.get('center').select('name.scientific_name').on('contextmenu', showContext );
		Ext.get('center').select('a').on('click', followLink );
		return true;
	}

});