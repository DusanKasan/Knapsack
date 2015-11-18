var query = window.location.search;
var branch = query ? query.replace("?", "") : 'master';

$.ajax({
	url: "https://rawgit.com/DusanKasan/Knapsack/"+branch+"/README.md",
	dataType: 'text',
	success: function(data) {
		convertAndRenderMarkdown(data);
		highlightPhpCode();
		fillOperationLists();
		buildHeader();
		hideDocumentationSection();
		restoreToHashPosition();
		hideLoader();
	}
});

function hideLoader() {
	$('.loader-container').hide();
}

function convertAndRenderMarkdown(data) {
	var converter = new Markdown.Converter();
	converter.hooks.chain("preBlockGamut", function (text, runBlockGamut) {
		return text.replace(/^ {0,3}```php *\n((?:.*?\n)+?) {0,3}``` *$/gm, function (whole, inner) {
			return "<pre><code>" + inner + "</code></pre>\n";
		});
	});
	$(".wrapper").html(converter.makeHtml(data));
}

function highlightPhpCode() {
	$('pre code').each(function (i, block) {
		hljs.highlightBlock(block);
	});
}

function fillOperationLists()
{
	var list = _.chain($('h4'))
		.groupBy(function (h4) {
			return $(h4).prevAll('h2:first').text();
		})
		.map(function(section, sectionName) {
			var html = _.chain(section)
				.indexBy(function(h4) {
					var section = $(h4).prevAll('h2:first').text().replace(' ', '-');
					return section + '-' + h4.innerHTML.split('(')[0].trim();
				})
				.each(function(h4, key) {h4.id = key;})
				.map(function (h4, key) {
					return '<li><a href="#' + key + '">' + key.split('-').pop() + '</a></li>'
				})
				.reduce(function (temp, listItem) {
					return temp + listItem;
				})
				.value();

			return {
				title: '<li><span class="operations-title">'+sectionName+'</span></li>',
				html: html
			};
		})
		.reduce(function(temp, sectionData) {
			return temp + sectionData.title + sectionData.html;
		}, '')
		.value();

	$('#operations-list-sidebar, #operations-list-dropdown').html(list);
}

function buildHeader() {
	$('h1, h1+p, h1+p+p').wrapAll('<div id="header"></div>');
	$('#header').append('<a href="https://github.com/DusanKasan/Knapsack">github.com/DusanKasan/Knapsack</a>');
}

function hideDocumentationSection() {
	$('h2:first-of-type, h2:first-of-type+p').hide()
}

function restoreToHashPosition() {
	if (clicked == null || clicked == true) {
		var original = location.hash;
		location.hash = '';
		location.hash = original;
	}
}

var clicked = null;
$('body').on('hidden.bs.collapse', 'nav', function () {
	restoreToHashPosition();
	$('.navbar-brand').addClass('hidden');
}).on('click', '.navbar li a', function() {
	clicked = true;
	$('.navbar-toggle').click();
}).on('show.bs.collapse', 'nav', function () {
	clicked = false;
	$('.navbar-brand').removeClass('hidden');
});
