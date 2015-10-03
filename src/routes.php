<?php
// MAIN
$app->get('/', 'controllers.index:indexAction')
    ->bind('index');
	
$app->get('/error/{code}', 'controllers.index:errorAction')
	->bind('error');

$app->post('/search', 'controllers.index:indexSearchAction')
    ->bind('index_search');

$app->get('/result_search/{search}', 'controllers.index:indexSearchDatatablesAction')
    ->bind('index_search_datatables');

$app->get('/read/{id}', 'controllers.index:readAction')
	->bind('read');

$app->get('/read_pdf/{id}', 'controllers.index:readPDFAction')
	->bind('read_pdf');

$app->get('/last_poem', 'controllers.index:lastPoemAction')
	->bind('last_poem');

$app->get('/author/{id}', 'controllers.index:authorAction')
	->bind('author');

$app->get('/author_poem_datatables/{authorId}', 'controllers.index:authorDatatablesAction')
	->bind('author_poem_datatables');

$app->get('/byauthors', 'controllers.index:byAuthorsAction')
    ->bind('byauthors');

$app->get('/byauthors_datatables', 'controllers.index:byAuthorsDatatablesAction')
    ->bind('byauthors_datatables');
	
$app->get('/collection/{id}', 'controllers.index:collectionAction')
	->bind('collection');
	
$app->get('/collection_poem_datatables/{collectionId}', 'controllers.index:collectionDatatablesAction')
	->bind('collection_poem_datatables');
	
$app->get('/bycollections', 'controllers.index:byCollectionsAction')
    ->bind('bycollections');
	
$app->get('/bycollections_datatables', 'controllers.index:byCollectionsDatatablesAction')
    ->bind('bycollections_datatables');

$app->get('/poeticform/{id}', 'controllers.index:poeticFormAction')
	->bind('poeticform');
	
$app->get('/poeticform_poem_datatables/{poeticformId}', 'controllers.index:poeticformDatatablesAction')
	->bind('poeticform_poem_datatables');
	
$app->get('/bypoeticforms', 'controllers.index:byPoeticFormsAction')
    ->bind('bypoeticforms');
	
$app->get('/bypoeticforms_datatables', 'controllers.index:byPoeticFormsDatatablesAction')
    ->bind('bypoeticforms_datatables');

$app->get('/country/{id}', 'controllers.index:countryAction')
	->bind('country');

$app->get('/bycountries', 'controllers.index:byCountriesAction')
    ->bind('bycountries');
	
$app->get('/bycountries_datatables', 'controllers.index:byCountriesDatatablesAction')
    ->bind('bycountries_datatables');

$app->get('/bypoemusers', 'controllers.index:byPoemUsersAction')
    ->bind('bypoemusers');
	
$app->get('/bypoemusers_datatables', 'controllers.index:byPoemUsersDatatablesAction')
    ->bind('bypoemusers_datatables');

$app->get('/country_poem_datatables/{countryId}', 'controllers.index:countryDatatablesAction')
	->bind('country_poem_datatables');

$app->get('/about', 'controllers.index:aboutAction')
	->bind('about');

$app->get('/copyright', 'controllers.index:copyrightAction')
	->bind('copyright');

$app->get('/admin', 'controllers.admin:indexAction')
	->bind('admin');

$app->get('/stat_poem', 'controllers.index:statPoemAction')
	->bind('stat_poem');

// CAPTCHA
$app->get('/captcha', 'controllers.index:reloadCaptchaAction')
	->bind('captcha');

// GRAVATAR
$app->get('/gravatar', 'controllers.index:reloadGravatarAction')
	->bind('gravatar');
	
// COMMENT
$app->get('/comment/{poemId}', 'controllers.comment:indexAction')
	->assert('poemId', '\d+')
	->bind('comment');

$app->post('comment/create/{poemId}', 'controllers.comment:createAction')
	->assert('poemId', '\d+')
	->bind('comment_create');

$app->get('comment/load', 'controllers.comment:loadCommentAction')
	->bind('comment_load');
	
// POEMVOTE
$app->get('/vote_poem/{idPoem}', 'controllers.poemvote:voteAction')
	->bind('vote_poem');

// ADMIN AJAX
$app->post('/list_selected_biography', 'controllers.poemadmin:listSelectedBiographyAction')
	->bind('list_selected_biography');

$app->post('/list_selected_collection', 'controllers.poemadmin:listSelectedCollectionAction')
	->bind('list_selected_collection');

$app->get('/user/poem_user_datatables/{username}', 'controllers.user:poemsUserDatatablesAction')
	->bind('poem_user_datatables');

$app->get('/user/poem_vote_datatables/{username}', 'controllers.user:votesUserDatatablesAction')
	->bind('poem_vote_datatables');

$app->get('/user/poem_comment_datatables/{username}', 'controllers.user:commentsUserDatatablesAction')
	->bind('poem_comment_datatables');
	
// USER
$app->get('/user/login', 'controllers.user:connect')
	->bind('login');

$app->get('/user/list', 'controllers.user:listAction')
	->bind('list');

$app->get('/user/show/{username}', 'controllers.user:showAction')
	->value('username', false)
	->bind('user_show');

$app->get('/user/new', 'controllers.user:newAction')
	->bind('user_new');

$app->post('/user/create', 'controllers.user:createAction')
	->bind('user_create');

$app->get('/user/edit/{id}', 'controllers.user:editAction')
	->value('id', false)
	->bind('user_edit');

$app->post('/user/update/{id}', 'controllers.user:updateAction')
	->value('id', false)
	->bind('user_update');

$app->get('/user/updatepassword', 'controllers.user:updatePasswordAction')
	->bind('user_udpatepassword');

$app->post('/user/updatepasswordsave', 'controllers.user:updatePasswordSaveAction')
	->bind('user_updatepasswordsave');

$app->get('/user/forgottenpassword', 'controllers.user:forgottenPasswordAction')
	->bind('user_forgottenpassword');

$app->post('/user/forgottenpasswordsend', 'controllers.user:forgottenPasswordSendAction')
	->bind('user_forgottenpasswordsend');

// POEM USER
$app->get('/poemuser/new', 'controllers.index:poemUserNewAction')
    ->bind('poemuser_new');

$app->post('/poemuser/create', 'controllers.index:poemUserCreateAction')
	->bind('poemuser_create');

$app->get('/poemuser/edit/{id}', 'controllers.index:poemUserEditAction')
    ->bind('poemuser_edit');

$app->post('/poemuser/update/{id}', 'controllers.index:poemUserUpdateAction')
	->bind('poemuser_update');

$app->get('/poemuser/delete', 'controllers.index:poemUserDeleteAction')
	->bind('poemuser_delete');

// CONTACT
$app->get('/contact', 'controllers.contact:indexAction')
    ->bind('contact');
	
$app->post('/contact_send', 'controllers.contact:sendAction')
	->bind('contact_send');
	
// ADMIN POETIC FORM
$app->get('/admin/poeticform/index', 'controllers.poeticformadmin:indexAction')
    ->bind('poeticformadmin_index');

$app->get('/admin/poeticform/indexdatatables', 'controllers.poeticformadmin:indexDatatablesAction')
    ->bind('poeticformadmin_indexdatatables');

$app->get('/admin/poeticform/new', 'controllers.poeticformadmin:newAction')
    ->bind('poeticformadmin_new');

$app->post('/admin/poeticform/create', 'controllers.poeticformadmin:createAction')
    ->bind('poeticformadmin_create');

$app->get('/admin/poeticform/show/{id}', 'controllers.poeticformadmin:showAction')
    ->bind('poeticformadmin_show');

$app->get('/admin/poeticform/edit/{id}', 'controllers.poeticformadmin:editAction')
    ->bind('poeticformadmin_edit');

$app->post('/admin/poeticform/upate/{id}', 'controllers.poeticformadmin:updateAction')
    ->bind('poeticformadmin_update');
	
// ADMIN COUNTRY
$app->get('/admin/country/index', 'controllers.countryadmin:indexAction')
    ->bind('countryadmin_index');

$app->get('/admin/country/indexdatatables', 'controllers.countryadmin:indexDatatablesAction')
    ->bind('countryadmin_indexdatatables');

$app->get('/admin/country/new', 'controllers.countryadmin:newAction')
    ->bind('countryadmin_new');

$app->post('/admin/country/create', 'controllers.countryadmin:createAction')
    ->bind('countryadmin_create');

$app->get('/admin/country/show/{id}', 'controllers.countryadmin:showAction')
    ->bind('countryadmin_show');

$app->get('/admin/country/edit/{id}', 'controllers.countryadmin:editAction')
    ->bind('countryadmin_edit');

$app->post('/admin/country/upate/{id}', 'controllers.countryadmin:updateAction')
    ->bind('countryadmin_update');
	
// ADMIN BIOGRAPHY
$app->get('/admin/biography/index', 'controllers.biographyadmin:indexAction')
    ->bind('biographyadmin_index');

$app->get('/admin/biography/indexdatatables', 'controllers.biographyadmin:indexDatatablesAction')
    ->bind('biographyadmin_indexdatatables');

$app->get('/admin/biography/new', 'controllers.biographyadmin:newAction')
    ->bind('biographyadmin_new');

$app->post('/admin/biography/create', 'controllers.biographyadmin:createAction')
    ->bind('biographyadmin_create');

$app->get('/admin/biography/show/{id}', 'controllers.biographyadmin:showAction')
    ->bind('biographyadmin_show');

$app->get('/admin/biography/edit/{id}', 'controllers.biographyadmin:editAction')
    ->bind('biographyadmin_edit');

$app->post('/admin/biography/upate/{id}', 'controllers.biographyadmin:updateAction')
    ->bind('biographyadmin_update');

// ADMIN COLLECTION
$app->get('/admin/collection/index', 'controllers.collectionadmin:indexAction')
    ->bind('collectionadmin_index');

$app->get('/admin/collection/indexdatatables', 'controllers.collectionadmin:indexDatatablesAction')
    ->bind('collectionadmin_indexdatatables');

$app->get('/admin/collection/new', 'controllers.collectionadmin:newAction')
    ->bind('collectionadmin_new');

$app->post('/admin/collection/create', 'controllers.collectionadmin:createAction')
    ->bind('collectionadmin_create');

$app->get('/admin/collection/show/{id}', 'controllers.collectionadmin:showAction')
    ->bind('collectionadmin_show');

$app->get('/admin/collection/edit/{id}', 'controllers.collectionadmin:editAction')
    ->bind('collectionadmin_edit');

$app->post('/admin/collection/upate/{id}', 'controllers.collectionadmin:updateAction')
    ->bind('collectionadmin_update');

// ADMIN POEM
$app->get('/admin/poem/index', 'controllers.poemadmin:indexAction')
    ->bind('poemadmin_index');

$app->get('/admin/poem/indexdatatables', 'controllers.poemadmin:indexDatatablesAction')
    ->bind('poemadmin_indexdatatables');

$app->get('/admin/poem/new', 'controllers.poemadmin:newAction')
    ->bind('poemadmin_new');

$app->post('/admin/poem/create', 'controllers.poemadmin:createAction')
    ->bind('poemadmin_create');

$app->get('/admin/poem/show/{id}', 'controllers.poemadmin:showAction')
    ->bind('poemadmin_show');

$app->get('/admin/poem/edit/{id}', 'controllers.poemadmin:editAction')
    ->bind('poemadmin_edit');

$app->post('/admin/poem/upate/{id}', 'controllers.poemadmin:updateAction')
    ->bind('poemadmin_update');

$app->get('/admin/poem/newfast', 'controllers.poemadmin:newFastAction')
    ->bind('poemadmin_newfast');

$app->post('/admin/poem/addfast', 'controllers.poemadmin:addFastAction')
    ->bind('poemadmin_addfast');

$app->get('/admin/poem/get_biographies', 'controllers.poemadmin:getBiographiesByAjaxAction')
	->bind('poemadmin_getbiographiesbyajax');

// ADMIN CONTACT FORM
$app->get('/admin/contact/index', 'controllers.contactadmin:indexAction')
    ->bind('contactadmin_index');

$app->get('/admin/contact/indexdatatables', 'controllers.contactadmin:indexDatatablesAction')
    ->bind('contactadmin_indexdatatables');

$app->get('/admin/contact/show/{id}', 'controllers.contactadmin:showAction')
    ->bind('contactadmin_show');