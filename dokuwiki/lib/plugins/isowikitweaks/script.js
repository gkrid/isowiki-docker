jQuery(function () {
	var table_border_hide_empty = function($this) {
		"use strict";
        $this.find("td").filter(function () {
            var $this = jQuery(this);
            if ($this.text().trim() === '') {
                return true;
            }
            return false;
        }).css("border-top", "0");
	};

	jQuery(".isowikitweaks-table-border-hide-empty").each(function() {
		var $this = jQuery(this);
        table_border_hide_empty($this);
	});

    jQuery(".isowikitweaks-merge-duplicates").each(function() {
        var $this = jQuery(this),
			ncolumns = $this.find("tr:first-child th").length;

        $this.find("td").each(function () {
        	"use strict";
        	jQuery(this).data('html', jQuery(this).html());
		});

        for (var i = 0; i < ncolumns; i++) {
        	$this.find("tr").each(function () {
        		var $tr = jQuery(this);
        		if ($tr.prev().find("td").eq(i).data('html') === $tr.find("td").eq(i).data('html')) {
                    $tr.find("td").eq(i).html('');
				}
			});
		}

        table_border_hide_empty($this);
    });

    jQuery(".isowikitweaks-rotate").each(function () {
        var $this = jQuery(this);
        var cells = $this.data('isowikitweaks-rotate');
        var $table = $this.find("table");
        jQuery.each(cells, function (index, cell) {
            var $cell = $table.find("tr").eq(cell.row).find("td,th").eq(cell.col);
            $cell.addClass("rotate-cell");
            $cell.wrapInner('<div class="rotate"></div>');
        });
    });
});

