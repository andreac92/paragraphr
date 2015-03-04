"use strict";
/**
 * The Paragraphr module, containing all the necessary functions and variables.
 * @module paragraphr
 */
var Paragraphr = (/** @lends module:paragraphr */ function() {
	/**
	 * The current stage of the application (write, edit, share).
	 * @var {string}
	 */
	var pScreen = 'write';
	/**
	 * User-chosen settings.
	 * @var {Object}
	 */
	var settings = { mode: 'normal', font: 'Georgia', fontSize: '15px' };
	/**
	 * The written document as a text string and an array of paragraph objects, along with
	 * its unique URL identifier that retrieves the document from the backend.
	 * @var {Object}
	 */
	var doc = { txt : '', pars : [], urlID: '' };
	/**
	 * The starting position of a paragraph in the 'edit' screen before being dragged.
	 * @var {Object}
	 */
	var parStartPos = '';
	/**
	 * Event binding applicable to all screens.
	 */
	var allScreensBindings = function() {
		$(".header-cog").click(function(){
			$("#app-area").toggleClass("full-screen");
		});
		$("#submit").click(function(){
			changeScreen();
		});
		$('select#fontface').change(function(){
			settings.font = $(this).val();
			var textBox = (pScreen == 'share') ? '#writing-area' : 'textarea';
			$(textBox).css('font-family', settings.font);
			setTimeout( function() {
		    	$(textBox).trigger('autosize.resizeIncludeStyle');
		    }, 300);
			console.log("font is: " + $(this).val());
		});
		$('select#fontsize').change(function(){
			settings.fontSize = $(this).val() + 'px';
			var textBox = (pScreen == 'share') ? '#writing-area' : 'textarea';
			$(textBox).css('font-size', settings.fontSize);
			setTimeout( function() {
		    	$(textBox).trigger('autosize.resizeIncludeStyle');
		    }, 300);
			console.log("font size is: " + $(this).val());
		});
	};
	/**
	 * Event binding applicable to the 'write' screen.
	 */
	var writeScreenBindings = function() {
		$(".chk").click(function(){
			settings.mode = ($(this).attr('id') == "focused-mode") ? 'focused' : 'normal';
			console.log("mode is: "+ settings.mode);
			$(".chk").toggleClass('chked');
			changeMode();
		});
	};
	/**
	 * Event binding applicable to the 'edit' screen.
	 */
	var editScreenBindings = function() {
		$('.show-hide').click(function(){
			console.log("here");
			$(this).closest('.par-intro').siblings('.par-body').toggle();
			if ($(this).closest('.par-intro').siblings('.par-body').is(":visible")) {
				$(this).removeClass("fa-chevron-circle-down").addClass("fa-chevron-circle-up");
			} else {
				$(this).removeClass("fa-chevron-circle-up").addClass("fa-chevron-circle-down");
			}
		});
		$('#i-colors div').click(function(){
			console.log("clicked");
			$('#i-colors div').removeClass('active-color');
			$(this).addClass('active-color');
			var color = $(this).css('background');
			$('.par-intro').css('background', color);
			console.log("color is: " + color);
		});
	};
	/**
	 * Event binding applicable to the 'share' screen.
	 */
	 var shareScreenBindings = function() {
	 	var url = (doc.urlID) ? doc.urlID : (window.location.href).slice(-5,-1);
	 	console.log(url);
	 	$('#click-dl').click(function(){
	 		console.log("clicked");
	 		$.ajax({
				type: 'POST',
				data: ({textfile:url}),
				url: "http://andrea-campos.com/paragraphr-dl/",
				success: function(data){
					if (data && data != -1){
						console.log("horrah");
						console.log(data);
						$("#click-dl").hide();
						$("#dl-button").css('display', 'block').attr('href', data);
					} else {
						console.log("hmm...");
					}
				},
				error: function(e){
					console.log("we got a problem: " + e.message);
				}
			});
	 		return false;
	 	})
	 };
	/**
	 * Change the mode (normal or focused) in the 'write' screen.
	 */
	var changeMode = function() {
		if (settings.mode == 'normal'){
			$("#writing-area").removeClass("focused").addClass("normal");
			var leftOver = $("#writing-doc").val();
			if (leftOver) doc.txt += (leftOver + "\n\n");
			$("#writing-doc").focus().val(doc.txt);
		} else {
			$("#writing-area").removeClass("normal").addClass("focused");
			doc.txt = $("#writing-doc").val();
			if (doc.txt) doc.txt += "\n\n";
			doc.txt = (doc.txt).replace(/\n{3,}/g,"\n\n");
			$("#writing-doc").val("").focus().keypress(function(e){
	  			if (settings.mode == 'focused' && 13 == e.keyCode) {
	  				console.log("here");
	  				doc.txt += $(this).val() + "\n\n";
	  				$(this).val("");
	  				return false;
	  			}
			});
		}
		$('textarea').trigger('autosize.resizeIncludeStyle'); 
	};
	/**
	 * Change to the subsequent screen.
	 */
	var changeScreen = function() {
		if (pScreen == 'write'){
			pScreen = 'edit';
			assembleParagraphs();
			editScreenBindings();
			$("#write-modes").hide();
			$("#change-color").show();
			$("#submit span").text("I'm done");
			$("#writing-area").removeClass();
			$("#step-title").html("<i class='fa fa-file-text-o'></i> edit");
			$("#writing-doc").remove();
			$("#help").html("<p>Edit your writing and work on creating a logical and orderly flow. You can drag paragraphs around to tweak order. Minimizing the body of each paragraph allows you to quickly assess whether your introduction sentence captures the point of the paragraph, and whether it contributes to the overall work and flow.</p><p>When you are done, click the button above.</p>");
		} 
		else if (pScreen == 'edit') {
			pScreen = 'share';
			$("#change-color").hide();
			$("#step-title").html("<i class='fa fa-share'></i> share");
			$("#submit span").text("write another");
			$("#help").html("<p>Share your writing with the world, or download a copy for yourself.</p><h5>Download</h5><a id='click-dl' href='#'><i class='fa fa-arrow-circle-o-down'></i> As text file.</a><a id='dl-button' href='#'>Download</a><p>You can also access your writing at the following URL for 60 days, after which it will be permanently deleted, so make sure it's saved before then :</p><p id='w-url'></p>");
			$('.par-edit').remove();
			var finalDoc = consolidatePars();
			$("#writing-area").append(finalDoc).css({'font-size': settings.fontSize, 'font-family': settings.font}).sortable( "destroy" );
			$.ajax({
				type: 'POST',
				data: JSON.stringify(doc.pars),
				url: "http://andrea-campos.com/osesfeoge/",
				success: function(data){
					if (data && data != "none"){
						changeURL(data);
						shareScreenBindings();
					}
				},
				error: function(e){
					console.log("we got a problem: " + e.message);
				}
			});
		}
		else {
			window.location = "http://andrea-campos.com/paragraphr/";
		}
	};
	/**
	 * Insert the URL that a document can be found at into the #w-url element.
	 * @param {string} url - The 4 character unique url identifier for a document.
	 */
	var changeURL = function(url){
		console.log(url);
		doc.urlID = url;
		var newURL = window.location.href + url;
		$("#w-url").text(newURL);
	}
	/**
	 * Consolidate the paragraph objects into an HTML string.
	 * @returns {string}
	 */
	 var consolidatePars = function(){
	 	var text = '';
	 	var par = '';
	 	for (var i = 0; i < (doc.pars).length; i++){
				par = ((doc.pars)[i]).intro + " ";
				par += ((doc.pars)[i]).pbody;
				par = par.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	 			par = "<p>" + par + "</p>";
	 			text += par;
	 	}
	 	return text;
	 }
	/**
	 * Add the paragraphs to the 'edit' screen with functionality (drag/drop, event 
	 * handlers, textarea plugin).
	 */
	var assembleParagraphs = function() {
		addParagraphs();
		$('textarea').css({'font-size': settings.fontSize, 'font-family': settings.font}).height(settings.fontSize); // textarea size plugin
		$('textarea').autosize({append: false});
		$("#writing-area").sortable({
			placeholder: "sortable-placeholder",
			tolerance: "pointer",
			forcePlaceholderSize: true,
			start: startRearrange,
			stop: stopRearrange
		});
		$('.intro-sen').blur(function(){
			var index = $('.intro-sen').index($(this));
			((doc.pars)[index]).intro = $(this).val();
		});
		$('.par-body').blur(function(){
			var index = $('.par-body').index($(this));
			((doc.pars)[index]).pbody = $(this).val();
		});
	};
	/**
	 * Keep track of a paragraph's original position before being dragged in the 'edit'
	 * screen.
	 */
	 var startRearrange = function(event, ui){
	 	parStartPos = ui.item.index();
	 }
	 /**
	 * Swap paragraph objects after a paragraph has been moved to a new position in the
	 * 'edit' screen.
	 */
	 var stopRearrange = function(event, ui){
	 	var end = ui.item.index();
	 	var temp = (doc.pars)[parStartPos];
	 	(doc.pars)[parStartPos] = (doc.pars)[end];
	 	(doc.pars)[end] = temp;
	 	console.log("updated: " + doc.pars);
	 }
	/**
	 * Create paragraph objects from the text string of the written document, then 
	 * insert them into the containing element as HTML.
	 */
	var addParagraphs = function() {
		var units = parseDocument();
		for (var i=0; i < units.length; i++){
			if (units[i]){
				var paragraph = makeParagraph(units[i]);
				(doc.pars).push(paragraph);
				$("#writing-area").append(htmlifyParagraph(paragraph));
			}
		}
	};
	/**
	 * Take the entire written document, and reduce newlines to 1 between each paragraph.
	 * Split the paragraphs into an array.
	 * @returns {array}
	 */
	var parseDocument = function() {
		doc.txt = (settings.mode == 'normal') ? $("#writing-doc").val() : doc.txt + $("#writing-doc").val().replace(/\n{2,}/g,"\n");
		var paragraphs = (doc.txt).split("\n");
		doc.txt = '';
		return paragraphs;
	};
	/**
	 * Paragraph object.
	 * @class
	 * @param {string} intro - The intro sentence of the paragraph.
	 * @param {string} pbody - The body sentences of the paragraph.
	 */
	var paragraph = function(intro, pbody) {
		this.intro = intro;
		this.pbody = pbody;
	};
	/**
	 * Generate the HTML necessary to display a paragraph on the page.
	 * @param {Object} par - The paragraph object
	 * @returns {string}.
	 */
	var htmlifyParagraph = function(par) {
		var txt = '<div class="par-edit"><div class="par-intro"><textarea class="intro-sen text-font">';
		txt += par.intro;
		txt += '</textarea><div class="intro-icon"><i class="fa fa-chevron-circle-up fa-lg show-hide"></i><i class="fa fa-arrows fa-lg drag"></i></div></div><textarea class="par-body text-font">';
		txt += par.pbody;
		txt += 	'</textarea></div>';
		return txt;
	};
	/**
	 * Make a paragraph object by searching for the introduction sentence.
	 * @param {string} - The paragraph as a text string.
	 * @returns {Object}
	 */
	var makeParagraph = function(par) {
		var body;
		var intro = par.search(/([^\.]\.|.[\?\!])[ ]/);
		if (intro == -1){
			body = '';
			intro = par;
		} else {
			body = par.slice(intro+2).trim();
			intro = par.slice(0,intro+2);
		}
		return new paragraph(intro, body);
	};
	/**
	 * Initialize the app.
	 */
	var init = (function() {
		allScreensBindings();
		if ($("#writing-area").hasClass("writing")) {
			console.log(window.location.href);
			$('textarea').autosize({append: false}); // textarea resize plugin
			writeScreenBindings();
		} else {
			pScreen = 'share';
			shareScreenBindings();
		}
	})();
} )();
