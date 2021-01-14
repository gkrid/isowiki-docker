jQuery(function() {

	function sort_list($list) {
		var elements = [];
		$list.find("> li").each(function() {
			var $this = jQuery(this);
			elements.push([$this.text(), $this]);
		});
		elements.sort(function(a, b) { return (a[0]).localeCompare(b[0]) });
		for (i in elements) {
			var $li = elements[i][1];
			$list.append($li);
		}
	}

	function group_by_letters($list) {
		var lists = {};

		$list.find("> li").each(function() {
			var $this = jQuery(this);
			var text = $this.text().trim();
			var firstLetter = text.charAt(0);
			if (!lists.hasOwnProperty(firstLetter)) {
				lists[firstLetter] = [];
			}
			lists[firstLetter].push($this);
		});

		var $levelDiv = $list.closest("div[class^=level]");
		if ($levelDiv.length === 0) {
			var headerLevel = 1;
		} else {
			var level =  $levelDiv.attr("class").substring("level".length);
			var headerLevel = parseInt(level) + 1;
		}

		var $parent = $list.parent();
		jQuery.each(lists, function (header, elements) {
			jQuery(document.createElement("h" + headerLevel)).text(header).appendTo($parent);
			jQuery($list[0].cloneNode()).appendTo($parent).append(elements);
		});
		$list.remove();
	}

	function init() {
		jQuery(".plugin__alphalist2").each(function() {
			var $this = jQuery(this);
			var classes = this.className.split(/\s+/);

			$this.find("ol, ul").each(function () {
				var $this = jQuery(this);
				sort_list($this);
				if (jQuery.inArray("group_by_letters", classes) !== -1) {
					group_by_letters($this);
				}
			});
		});
	}

	jQuery(init);

});
