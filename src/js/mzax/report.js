/**
 * Mzax Emarketing (www.mzax.de)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


// init main namespace
window.mzax = window.mzax || {};





(function(window, mzax) {


    function tabId(tab)
    {
        var tabId  = tab.getAttribute('data-tabId');
        if(!tabId) {
            tabId = tab.id;
        }
        return tabId;
    }



    mzax.ui = mzax.ui || {};


    mzax.disable = function() {
        Element.show('loading-mask').setStyle({position:'fixed', top:0, bottom:0, left:0, right:0, background:'rgba(255,255,255,0.9)'});
    };


    mzax.ui.TabBar = Class.create({

        initialize: function(list, defaultTab)
        {
            var scope = this;
            scope.list = list;
            scope.tabs = list.childElements();
            scope.defaultTab = defaultTab;

            scope.tabs.each(function(tab) {
                tab.on('click', function(e) {
                    scope.tabClick(this);
                });
                if(tabId(tab) == defaultTab) {
                    list.addClassName(defaultTab);
                    tab.addClassName('active');
                }
            });

            this.on = list.on.bind(list);
            this.observe = list.observe.bind(list);
            this.fire = list.fire.bind(list);
        },

        tabClick : function(tab)
        {
            var id  = tabId(tab);

            this.list.removeClassName(this.activeTab);
            this.activeTab = id;
            this.list.addClassName(id);

            this.tabs.each(function(tab) {
                Element.removeClassName(tab, 'active');
            });
            tab.addClassName('active');

            this.fire('tabbar:click', {tabbar: this, tab: tab, tabId: id});
        }

    });



    mzax.ui.FrameStack = Class.create({

        initialize: function(tabBar, container)
        {
            var scope = this;
            scope.container = container;
            scope.tabBar = tabBar;

            scope.stacks = container.childElements();
            scope.stacks.each(function(stack) {
                stack.hide();
            });

            tabBar.on('tabbar:click', function(event) {
                scope.show(event.memo.tabId+'_content');
            });

            if(tabBar.defaultTab) {
                scope.show(tabBar.defaultTab+'_content');
            }

        },


        hideAll : function()
        {
            this.stacks.each(function(stack) {
                stack.hide();
            });
        },

        show : function(stackId)
        {
            this.hideAll();
            var stack = this.container.down('#'+stackId);
            if(stack) {
                stack.show();
            }
        }


    });





})(window, window.mzax);






(function(window, mzax) {

// init namespace
mzax.report = {};






/**
 * CONSTANCES
 */
var VARIATION_CHECKBOX = '_varcb';
var VARIATION_SELECT = '_varsel';
var CHART_DIV = '_cdiv';
var BREADCRUMBS = '_brcm';
var TIME_UNIT_SELECT = '_tu_sel';

var PARAM_METRICS = 'metrics';
var PARAM_VARIATIONS = 'variations';
var PARAM_DIMENSION = 'dimension';
var PARAM_TIME_UNIT = 'time_unit';




var cache = {};


/**
 * Transforms a datatable from column to row
 * assuming columns are variations
 *
 * @param source
 * @returns
 */
function transposeTable(source)
{
    var target = new google.visualization.DataTable,
        totalColumns = source.getNumberOfColumns(),
        totalRows    = source.getNumberOfRows();

    target.addColumn ('string', 'Variation');

    for (var x=1; x < totalColumns; x++) {
        target.addRow([source.getColumnLabel(x)]);
    }

    for (var x=0; x < totalRows; x++) {
        target.addColumn('number', source.getValue(x,0));
        for (var y=1; y<totalColumns; y++)
            target.setValue(y-1, x+1, source.getValue(x,y));
    }
    return target;
}




function cacheKey(params)
{
    return ([params[PARAM_DIMENSION], params[PARAM_METRICS], params[PARAM_VARIATIONS], params[PARAM_TIME_UNIT]]).join('_');
}


function getCache(params)
{
    var key = cacheKey(params);
    return cache[key] ? cache[key] : false;
}

function setCache(params, data)
{
    cache[cacheKey(params)] = data;
    return data;
}


mzax.report.clearCache = function(){
    cache = {};
};



mzax.report.ChartBlock = Class.create({


    initialize: function(url, params)
    {
        this.url = url||'';
        this.params = params||{};
    },

    /**
     * Set chart data object
     *
     */
    setData: function(data, params)
    {
        this.data = new google.visualization.DataTable(data);
        setCache(params || this.params, this.data);
        return this;
    },


    /**
     * Set chart option object
     *
     */
    setOptions: function(options)
    {
        this.options = options||{};
        return this;
    },


    init: function(div)
    {
        var self = this;
        self.div = div;
        self[VARIATION_CHECKBOX] = div.down('input.variation-checkbox');
        self[TIME_UNIT_SELECT]   = div.down('select.time_unit');
        self[VARIATION_SELECT]   = div.down('select.variations');
        self[CHART_DIV]          = div.down('div.chart');

        self.chart    = new self.chartClass(self[CHART_DIV]);
        self.altChart = self.altChartClass ? new self.altChartClass(self[CHART_DIV]) : false;

        var tabList = div.down('ul.option-tabs');
        var tabs = tabList.childElements();
        var activeTab;

        if(tabs.length) {
           tabs[0].addClassName('active');
           tabList.addClassName(tabs[0].getAttribute('data-tabId'));
        }


        function tabClick(tab) {
            var tabId  = tab.getAttribute('data-tabId');
            var metric = tab.getAttribute('data-metric');

            tabList.removeClassName(activeTab);
            activeTab = tabId;
            tabList.addClassName(tabId);

            tabs.each(function(tab) {
                Element.removeClassName(tab, 'active');
             });
            tab.addClassName('active');

            self.load(metric);
        }


        tabs.each(function(tab) {
            tab.on('click', function(e) {
                tabClick(this);
            });
        });


        if(self[VARIATION_CHECKBOX]) {
            self[VARIATION_CHECKBOX].on('click', function() {
                self.load();
            });
        }

        if(self[TIME_UNIT_SELECT]) {
            self[TIME_UNIT_SELECT].on('change', function() {
                self.load();
            });
        }
        if(self[VARIATION_SELECT]) {
            self[VARIATION_SELECT].on('change', function() {
                self.load();
            });
        }


        // Little tab drop-down menu
        div.select('.drop-arrow').each(function(btn)
        {
            var tab = btn.up('.tab');
            var popup = tab.down('.metric-options');
            var timeoutId;

            btn.on('click', function(e) {
                popup.show();
                e.stop();
            });
            tab.on('mouseenter', function(e) {
                clearTimeout(timeoutId);
            });

            tab.on('mouseleave', function(e) {
                timeoutId = popup.hide.bind(popup).delay(0.5);
            });

            popup.select('.option').each(function(opt) {
                opt.on('click', function(e) {
                    var metric = opt.getAttribute('data-metric');
                    tab.down('.title').innerHTML = opt.innerHTML.strip().stripTags();
                    tab.setAttribute('data-metric', metric);
                    popup.hide();
                    clearTimeout(timeoutId);
                });
            });


        });


        var drawChart = self.draw.bind(self);
        Event.observe(window, "resize", drawChart);
        Event.observe(window, "dom:loaded", drawChart);
        setTimeout(drawChart, 10);
    },



    /**
     * Load new data from server
     *
     */
    load: function(metric)
    {
        var self = this, params = this.params, data;

        if(metric) {
            params[PARAM_METRICS] = [metric];
        }
        if(self[VARIATION_CHECKBOX]) {
            params[PARAM_VARIATIONS] = !!self[VARIATION_CHECKBOX].checked;
        }

        if(self[TIME_UNIT_SELECT]) {
            params[PARAM_TIME_UNIT] = self[TIME_UNIT_SELECT].value;
        }

        if(self[VARIATION_SELECT]) {
            var variation = self[VARIATION_SELECT].value;
            if(variation != -1) {
                params[PARAM_VARIATIONS] = variation;
            }
        }


        if(data = getCache(params)) {
            self.data = data;
            return self.draw();
        }

        params.c = Math.random();

        //console.log(params);

        self.div.addClassName('loading');
        new Ajax.Request(self.url, {
            postBody: Object.toJSON(params),
            evalJSON: true,
            contentType: 'application/json',
            onComplete: function(transport) {
                self.setData(transport.responseJSON, params);
                self.div.removeClassName('loading');
                self.draw();
            }.bind(this)
        });

        return self;
    },




    /**
     * Draw chart
     */
    draw: function()
    {
        try {
            var self = this,
                options = self.options,
                data = self.data,
                params = self.params;

            if(!data || !params || !options) {
                console.error("Missing data");
                console.log(this);
                return this;
            }

            if(!data.getNumberOfColumns() || !data.getNumberOfRows()) {
                self.div.down('.no-data').show();
                self[CHART_DIV].hide();
                return this;
            }
            self.div.down('.no-data').hide();
            self[CHART_DIV].show();


            switch(data.getTableProperty('timeunit')) {
                case 'days':
                    options.bar.groupWidth = '100%';
                    break;
                case 'weeks':
                    options.bar.groupWidth = '90%';
                    break;
                default:
                    options.bar.groupWidth = '90%';
                    break;
            }

            // check if first column is percentage
            if(data.getColumnId(1).match(/_rate/)) {
                options.vAxis.format = 'percent';
            }
            if(data.getColumnId(1).match(/_revenue/)) {
                options.vAxis.format = 'currency';
            }
            else {
                options.vAxis.format = '#,###';
            }


            if(data.getTableProperty('dye')) {
                options.legend.alignment = 'start';
                options.legend.position = 'right';
            }
            else {
                options.legend.alignment = 'end';
                if(params[PARAM_VARIATIONS]) {
                    options.legend.position = 'in';
                }
                else {
                    options.legend.position = 'none';
                }
            }


            options.isStacked = !!data.getTableProperty('stacked');
            options.colors = self.getSeriesColors();


            /*
            if(self.altChart && self.params[PARAM_VARIATIONS]) {
                options.groupWidth = '75%';
                options.isStacked = true;

                self.chart.clearChart();
                self.altChart.draw(transposeTable(self.data), options);
            }
            else {*/
                /*if(self.altChart) {
                    self.altChart.clearChart();
                }*/
                self.chart.draw(self.data, options);
           // }
        }
        catch(err) {
            console && console.error(err);
        }
        return self;
    },





    /**
     * Retrieve color array for all series
     *
     */
    getSeriesColors: function()
    {
        var colors = [];
        var data = this.data;
        var i = data.getNumberOfColumns();

        if(!data.getTableProperty('dye')) {
            while(--i > 0) {
                colors.unshift(data.getColumnProperty(i, 'color') || null);
            }
        }
        else {
            i = data.getNumberOfRows();
            while(--i > -1) {
                colors.unshift(data.getRowProperty(i, 'color') || null);
            }
        }
        return colors;
    }



});






mzax.report.GeoChartBlock = Class.create(mzax.report.ChartBlock, {


    init: function($super, div)
    {
        $super(div);
        var self = this;

        self[BREADCRUMBS] = div.down('.breadcrumbs');

        self[BREADCRUMBS].on('click', function() {
            self.loadRegion(null);
        })

        google.visualization.events.addListener(self.chart, 'regionClick', regionClick);

        function regionClick(e) {
            if(e.region.match('^[A-Z]{2}$')) {
                self.loadRegion(e.region);
            }
        }

    },


    loadRegion: function(region)
    {
        var params = this.params;
        var options = this.options;

        params[PARAM_DIMENSION] = region ? 'region' : 'country';
        options.region = region ? region : 'world';
        options.resolution = region ? 'provinces' : null;

        this[BREADCRUMBS].down('.region').innerHTML = region||'';
        this[BREADCRUMBS].down('.region').toggle(!!region);

        this.load();
    },



    draw: function()
    {
        var self = this;

        if(!self.data.getNumberOfColumns() || !self.data.getNumberOfRows()) {
            self.div.down('.no-data').show();
            self[CHART_DIV].hide();
            return this;
        }
        self.div.down('.no-data').hide();
        self[CHART_DIV].show();

        try {
            var options = this.options;

            options.datalessRegionColor = 'ddd';
            options.colorAxis = {colors: this.data.getColumnProperty(1, 'color_axis')};
            options.height = Math.max(Math.min(window.innerHeight-150, 800), 200);

            this.chart.draw(this.data, options);
        }
        catch(err) {
            console && console.error(err);
        }
    }

});




})(window, mzax);


