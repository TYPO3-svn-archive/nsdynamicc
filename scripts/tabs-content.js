/**!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */

Ext.onReady(function(){
    //quick edit event for each and individual row item    
    //remove actoin of help link
    Ext.select('a.quick-edit').on('click', function(e,traget){
        var currentRowID = traget.up('tr').id;
        //hide the row item form the record table for view quick edit
        Ext.get(currentRowID).hide();
    });
    
    // second tabs built from JS
    var tabs2 = new Ext.TabPanel({
        renderTo: 'typo3-listOptions',
        activeTab: 0,
        frame:true,
        width:600,
        height:250,
        plain:true,
        defaults:{autoScroll: true},
        items:[{
                title: 'Normal Tab',
                html: "My content was added during construction."
            },{
                title: 'Ajax Tab 1',
                autoLoad:'ajax1.htm'
            },{
                title: 'Ajax Tab 2',
                autoLoad: {url: 'ajax2.htm', params: 'foo=bar&wtf=1'}
            },{
                title: 'Event Tab',
                listeners: {activate: handleActivate},
                html: "I am tab 4's content. I also have an event listener attached."
            },{
                title: 'Disabled Tab',
                disabled:true,
                html: "Can't see me cause I'm disabled"
            }
        ]
    });

    function handleActivate(tab){
        alert(tab.title + ' was activated.');
    }
});