
bez.ctl.thread = function() {

	jQuery('.bez_commcause_delete_prompt').click('on', function (event) {
		if (!window.confirm(LANG.plugins.bez.remove_confirm)) {
            event.preventDefault();
		}
	});

	var $bez_comment_form = jQuery('.bez_comment_form'),
		$tabs = $bez_comment_form.find('.bez_tabs');
	if ($tabs.length > 0) {
		var $links = $tabs.find('a'),
			$active = $tabs.find('li.active a'),
			active	= $active[0],
			$comment_type_input = jQuery(".bez_comment_type"),
			$cause_type_div = jQuery(".bez_cause_type");

		var setActive = function($a) {
			$tabs.find('.active').removeClass('active');
			$a.parent().addClass('active');
            $a.parent().find('input[name=type]').click();
		};

        var $action_buttons = $bez_comment_form.find("button[value=thread_close], button[value=thread_reject]");

		var activateComment = function () {
			$comment_type_input.removeAttr("disabled");
			$cause_type_div.hide();
			$cause_type_div.find("input").attr("disabled", "disabled");

			$bez_comment_form.removeClass('bez_cause');
            $action_buttons.show();
		};

		var activateCause = function() {
			$comment_type_input.attr("disabled", "disabled");
			$cause_type_div.show();
			$cause_type_div.find("input").removeAttr("disabled");

			$bez_comment_form.addClass('bez_cause');
            $action_buttons.hide();
		};

		if (active.hash === '#comment') {
			setActive($active);
			activateComment();
		} else if (active.hash === '#cause') {
			setActive($active);
			activateCause();
		}


		$tabs.on('click', 'a', function (e) {
			e.preventDefault();
			setActive(jQuery(this));
		});

		$tabs.on('click', 'a[href="#comment"]', activateComment);
		$tabs.on('click', 'a[href="#cause"]', activateCause);
	}
    if ($bez_comment_form.length > 0) {
		//textareas
		var $textarea = $bez_comment_form.find("textarea");

        var $close_button = $bez_comment_form.find("button[value=thread_close]");
        var $reopen_button = $bez_comment_form.find("button[value=thread_reopen]");
        var $reject_button = $bez_comment_form.find("button[value=thread_reject]");

		var $header = $bez_comment_form.find(".bez_toolbar");
		bez.rich_text_editor($textarea, $header);
    }

	//show/hide comments
	var $show_comments = jQuery(".bez_show_comments"),
		$hide_comments = jQuery(".bez_hide_comments");

	var bez_show_comments = function() {
		$show_comments.hide();
		$hide_comments.show();
		localStorage.setItem('comments_are_hidden', '0');
		jQuery('.bez_type_0').show();
	};

	var bez_hide_comments = function() {
		$hide_comments.hide();
		$show_comments.show();
		localStorage.setItem('comments_are_hidden', '1');
		jQuery('.bez_type_0').hide();
	};

	var comments_are_hidden = localStorage.getItem('comments_are_hidden');
	if (comments_are_hidden === null || comments_are_hidden === '0') {
		bez_show_comments();
	} else {
		bez_hide_comments();
	}

	$show_comments.on('click', function(e) { e.preventDefault(); bez_show_comments() });
	$hide_comments.on('click', function(e) { e.preventDefault(); bez_hide_comments() });

    jQuery("#plugin__bez_thread_change_state_button").click(function() {
        "use strict";
        jQuery("button[value=thread_close], button[value=thread_reject], button[value=thread_reopen]").effect("highlight");
    });

	if (jQuery('.bez_task_form').length > 0) {
		var $task_form = jQuery('.bez_task_form');
		//date picker
		jQuery("input[name=plan_date]").datepicker({
			dateFormat: "yy-mm-dd"
			});
		if (jQuery("input[name=all_day_event]").is(":checked")) {
			jQuery('#task_datapair').hide();
		}
		jQuery("input[name=all_day_event]").on('change', function() {
			if (jQuery(this).is(":checked")) {
				jQuery('#task_datapair').hide();
			} else {
				jQuery('#task_datapair').show();
			}
		});

		//time picker
		jQuery('#task_datapair .time').timepicker({
				'showDuration': true,
				'timeFormat': 'H:i'
			});
		var timeDatepair = new Datepair(jQuery('#task_datapair').get(0));

		bez.rich_text_editor($task_form.find('textarea'), $task_form.find('.bez_toolbar'));

	}


	if (jQuery('#opinion').length > 0) {
		bez.rich_text_editor(jQuery('#opinion'), jQuery('.bez_opinion_toolbar'));
	}

	if (jQuery('#reason').length > 0) {
        jQuery('#no_evaluation').on('change', function() {
            if (jQuery(this).prop('checked') === true) {
                jQuery('#reason').prop('disabled', true).hide();
                jQuery('.bez_reason_toolbar').hide();
            } else {
                jQuery('#reason').prop('disabled', false).show();
                jQuery('.bez_reason_toolbar').show();
            }

        });
		bez.rich_text_editor(jQuery('#reason'), jQuery('.bez_reason_toolbar'));
	}

	//tooltips
	jQuery(document).tooltip({
		items: '#issue_participants a[title]',
		position: {
			my: "left top+15",
			at: "left bottom",
			collision: "flipfit"
		},
		content: function() {
			var $this = jQuery(this),
				name = $this.find('.bez_name').text(),
				content = '<div style="margin-bottom: 3px;">'+name+'</div>';
			$this.find('.bez_awesome').each(function() {
				var $this = jQuery(this);
				content += '<div>'+$this.get(0).outerHTML+' '+$this.attr('title')+'</div>';
			});

			return content;
		}
	});

	jQuery('#issue_participants .participant_remove').hide().click('on', function (event) {
        if (!window.confirm(LANG.plugins.bez.remove_confirm)) {
            event.preventDefault();
        }
    });
    jQuery('#issue_participants li').hover(
    	function() {
    		"use strict";
    		jQuery(this).find('.participant_remove').show();
		},
		function() {
    		"use strict";
            jQuery(this).find('.participant_remove').hide();
		}
	);


    //INVITE USERS
    jQuery.widget( "custom.inviteUsersCombobox", {
      _create: function() {
        this.wrapper = jQuery( "<span>" )
          .addClass( "custom-inviteUsersCombobox" )
          .insertAfter( this.element );

        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
      },

      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";

        this.input = jQuery( "<input>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-inviteUsersCombobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: jQuery.proxy( this, "_source" )
          })
          .tooltip({
            classes: {
              "ui-tooltip": "ui-state-highlight"
            }
          });

        this._on( this.input, {
          autocompleteselect: function( event, ui ) {
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },

          autocompletechange: "_removeIfInvalid"
        });
      },

      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;

        jQuery( "<a>" )
          .attr( "tabIndex", -1 )
          .attr( "title", LANG.plugins.bez.combobox_show_all_items )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-inviteUsersCombobox-toggle ui-corner-right" )
          .on( "mousedown", function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .on( "click", function() {
            input.trigger( "focus" );

            // Close if already visible
            if ( wasOpen ) {
              return;
            }

            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },

      _source: function( request, response ) {
        var matcher = new RegExp( jQuery.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = jQuery( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) ) {

            return {
              label: text,
              value: text,
              option: this
            };
          }
        }) );
      },

      _removeIfInvalid: function( event, ui ) {

        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }

        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( jQuery( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });

        // Found a match, nothing to do
        if ( valid ) {
          return;
        }

        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " " + LANG.plugins.bez.combobox_did_not_match )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.autocomplete( "instance" ).term = "";
      },

      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });

    jQuery( "#bez_invite_users select" ).inviteUsersCombobox();
    //INVITE
};
