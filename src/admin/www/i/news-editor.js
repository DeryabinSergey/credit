$(document).ready(function(){
    tinymce.init({
	selector: '#text',
	plugins: [
	    'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
	    'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
	    'table emoticons template paste help'
	],
	toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
	  'bullist numlist outdent indent | link image | print preview media fullpage | ' +
	  'forecolor backcolor emoticons | help',
	menu: {
	  favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | spellchecker | emoticons'}
	},
	spellchecker_languages: "Russian=ru",
	spellchecker_rpc_url: "https://speller.yandex.net/services/tinyspell",
	menubar: 'favs file edit view insert format tools table help'
    });
});