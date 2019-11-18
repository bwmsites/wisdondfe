<?php

Route::get('nfe/check-cadastro/{cnpj}/{modelo}', 'NFe\NFeController@checkCadastro');
Route::get('nfe/check-sefaz/{cnpj}/{modelo}', 'NFe\NFeController@checkSefaz');
Route::get('nfe/gera-xml/{cnpj}', 'NFe\NFeController@geraXML');
Route::get('nfe/assina-xml/{cnpj}', 'NFe\NFeController@assinaXML');

