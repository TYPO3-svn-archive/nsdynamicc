// Create the class
var DcolumnClass = Class.create({
		
		initialize:function(){
				this.obj = '';
				// Is called when the page has finished loading by the Event.observe code below
		var dClickIcon = $$('.dcolumn'); 
		dClickIcon.each(function(Element){
        //call helper function to do other cool stuff
    		Element.observe("click", function(event) {
						this.obj = event.element();																
						var getData = this.obj.up().readAttribute("data-dinfo");				
						var getDataObject = getData.split(",");
								
				var getGroups = new Ajax.Request('ajax.php', {
					method: 'get',
					parameters: 'ajaxID=tx_nsdynamicc::createAjaxSelector&id='+getDataObject[0]+'&table='+getDataObject[1],
						onComplete: this.showPopup.bind(this)
						});						
				}.bind(this));										
    }.bind(this));
		},
		
		showPopup: function(xhr){
				var HTMLoutput = xhr.responseText;
				//alert(HTMLoutput);
				this.obj.up().addClassName("disply-list");
				this.addSelectorBox(HTMLoutput);
				//event.target.			
		},
		
		/**
	 * manipulates the DOM to add the divs needed for selector box at the bottom of the <body>-tag
	 *	 
	 */
		addSelectorBox: function(HTMLoutput){				
				//add the content to the bottom of the <body> tag				
				var offset = this.obj.up().cumulativeOffset();						
				//Add custom styles				
				var container = '<div id="contentselectbox" style="display: block;top: '+offset[1]+'px; right: 45px">'+HTMLoutput+'</div>';
				//select box wrapper container object
				var selector = $('contentselectbox');

				this.obj.up('body').insert({
						bottom: container
				});
				
				//bind the mousedown event
				document.observe("mousedown", this.checkMouse.bind(this));
		},
		
		checkMouse: function(event){
				//alert('1')
    //check the click was on selector itself or on selectorOwner
    var selector = "#contentselectbox";
    //var selectorParent = $(event.target).parents(selector).length;

		var selectorParent = 0;
				selectorParent = event.target.up(selector);
				//check the mouse only click on the out of the target eara
				if( event.target == $("contentselectbox")[0] ||  event.target == this.obj.up() || Object.isElement(selectorParent)){			
				return false;
		}
				this.hideSelector();   
		},
		
		//hide and remver the loaded popup record list 
		hideSelector: function(){				
    var selector = $("contentselectbox");
		
		//remove entire selector container form the document 
    document.stopObserving("mousedown");
    selector.remove();    
  }

		
});
		
// Global variable for the instance of the class
var dcolumn;
// Creating an instance of the class if the page has finished loading
Event.observe(window, "load", function() {
		dcolumn = new DcolumnClass();
});