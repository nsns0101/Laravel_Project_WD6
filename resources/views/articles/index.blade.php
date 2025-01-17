@extends('layouts.app')

@section('content')
@php $viewName = 'articles.index'; @endphp

<div class="page-header">
    <h4>
        <a href="{{ route('articles.index') }}">
            포럼
        </a>
        <small>
            / 글 목록
        </small>
    </h4>
</div>

<div class="text-right action__article">
        <button class="fa fa-plus-circle btn btn__create__article  btn-primary"></i>
        새 글 쓰기
</div>
<div class = "main_article">
    <div class="row container__article">
        <div class="col-md-3 sidebar__article">
            <aside>
                <!-- 게시판 좌측 태그 목록 -->
                    @include('tags.partial.index')
            </aside>
        </div>

        <div class="col-md-9 list__article">
            @include('articles.create')

            <article>
                <!-- 게시글 목록 -->
                @forelse($articles as $article)
                @include('articles.partial.article', compact('article'))
                @empty
                <p class="text-center text-danger">
                    글이 없습니다.
                </p>
                @endforelse
            </article>

            @if($articles->count())
            <div class="text-center paginator__article">
                {!! $articles->render() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@stop
@section('script')
<script>

//게시글 눌렀을 경우의 ajax참고 ----------------------
var article_id = null;                  //article_id
    //게시글 열고 닫고의 토글기능을 위한 변수
var article = [];
var count = "{{$articles->count()}}";
for(var i = 1; i < count+1; i++){
    article[i] = 0;
}
//-----------------------------------------------


//새글쓰기 버튼
var count = 0;
$(document).on('click', '.btn__create__article', function(e) {
    article[article_id] = 0;
    count += 1;
    var text= count%2 == 0 ? " 새 글 쓰기" : " 돌아가기"
    if(!'{{auth()->user()}}'){
        alert("로그인 한 유저만 글 작성이 가능합니다");
        return;
    }
    var el_create = $('.new_article');
    var el_container = $('.container__article');
    el_container.toggle('fast').focus();
    el_create.toggle('fast').focus();
    $('.btn__create__article').html(text);
});

//게시글 작성
$(document).on('click', '.btn__save__article', function(e) {
    var form = $('#article_create_form')[0];
    var data = new FormData(form);
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        type: 'POST',
        enctype:"multipart/form-data",
        url: 'articles',
        data: data,
        processData: false,
        contentType: false,
    }).then(function (){
        article[article_id] = 0;
        $('.main_article').load('/articles .container__article');
        var el_create = $('.new_article');
        var el_container = $('.container__article');
        el_container.toggle('fast').focus();
        el_create.toggle('fast').focus();
    });
});

//선택한 태그인 게시글만 보여줌
$(document).on('click', '.btn__tag__article', function(e) {
    var tag = $(this).closest('.btn__tag__article').data('id');
    $.ajax({
        type: 'GET',
        url: `tags/${tag}/articles`,
    }).then(function (data){
        article[article_id] = 0;
        $('.main_article').load(`tags/${tag}/articles .container__article`);
    });
});
//게시글 눌렀을 경우
$(document).on('click', '.btn__show__article', function(e) {
    article_id = $(this).closest('.btn__show__article').data('id');
    article[article_id] +=1;
    $.ajax({
        type: 'GET',
        url: `/articles/${article_id}`,
    }).then(function (){
        if(article[article_id]%2 == 1){
            $(`.media${article_id}`).load(`/articles/${article_id} .list__article`);
        }
        else{
            $(`.media${article_id}`).load(`/articles .media${article_id}`);
        }
    });
});

//글 목록 버튼
$(document).on('click', '.button__list__articles', function(e) {
    $.ajax({
        type: "GET",
        url: '/articles'
    }).then(function() {
        article[article_id] = 0;
        $('#main_container').load(`/articles #main_container`);
    });
});

//게시글 수정 버튼
$(document).on('click', '.button__edit__articles', function(e) {
    article_id = $(this).closest('.button__edit__articles').data('id');

    $.ajax({
        type: "GET",
        url: `/articles/${article_id}/edit`
    }).then(function() {
        article[article_id] = 0;
        $(`.media${article_id}`).load(`/articles/${article_id}/edit #main_container`);
    });
});

//게시글 수정 완료 버튼
$(document).on('click', '.button__update__articles', function(e) {
    var form = $(`#article_edit_form${article_id}`)[0];
    var data = new FormData(form);
    data.append('_method', 'PUT');
    console.log(article_id);
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        type: 'POST',
        enctype:"multipart/form-data",
        url: `/articles/${article_id}`,
        data: data,
        processData: false,
        contentType: false,
    }).then(function (){
        article[article_id] = 0;
        $('.main_article').load('/articles .container__article');
    });
});
//게시글 삭제 버튼
$(document).on('click', '.button__delete__articles', function(e) {
    var article_id = $(this).closest('.button__delete__articles').data('id');

    if (confirm('글을 삭제합니다.')) { //글을 삭제합니다 경고창에서 yes를누르면 true
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
            type: "DELETE",
            url: '/articles/' + article_id
        }).then(function() {
            article[article_id] = 0;
            $('#main_container').load(`/articles #main_container`);
        });
    }
});

//댓글 생성
$(document).on('click', '.btn__create__comment', function(e) {
    var article_id = $(this).closest('.btn__create__comment').data('id');
    var parent_id =  $(this).closest('.item__comment').data('id');  //대댓글이면 부모 댓글id, 아니면 null
    
    if(parent_id){
        var content = $(`#${article_id}new_comment${parent_id}`).val();
    }
    else{
        var content = $(`#${article_id}new_comment`).val();
    }
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        type: 'POST',
        url: `/articles/${article_id}/comments`,
        data : {
            'content' : content,
            'commentable_id' : article_id,
            'parent_id' : parent_id,
        }
    }).then(function (){
        $(`.container__comment${article_id}`).load(`/articles/${article_id} .container__comment${article_id}`);
    });
});
//댓글 수정
$(document).on('click', '.btn__update__comment', function(e) {
    var article_id = $(this).closest('.btn__update__comment').data('id');
    var parent_id =  $(this).closest('.item__comment').data('id');  //대댓글이면 부모 댓글id, 아니면 null
    var content = $(`#edit_comment${parent_id}`).val();
    
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        type: 'PUT',
        url: '/comments/' + parent_id,
        data : {
            'content' : content,
            'commentable_id' : article_id,
        }
    }).then(function (){
        $(`.container__comment${article_id}`).load(`/articles/${article_id} .container__comment${article_id}`);
    });
});
//댓글 삭제
$(document).on('click', '.btn__delete__comment', function(e) {
    var commentId = $(this).closest('.item__comment').data('id');
    if (confirm('댓글을 삭제합니다.')) {
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
            type: 'DELETE',
            url: "/comments/" + commentId,
        }).then(function() {
            $('#comment_' + commentId).fadeOut(1000, function () { $(this).remove(); });
        });
    }
});

//답글쓰기버튼 textarea toggle
$(document).on('click', '.btn__reply__comment', function(e) {
    var el__create = $(this).closest('.item__comment').find('.media__create__comment').first(),
    el__edit = $(this).closest('.item__comment').find('.media__edit__comment').first();
    el__edit.hide('fast');
    el__create.toggle('fast').end().find('textarea').focus();
});

//댓글 수정버튼 textarea toggle
$(document).on('click', '.btn__edit__comment', function(e) {
    var el__create = $(this).closest('.item__comment').find('.media__create__comment').first(),
    el__edit = $(this).closest('.item__comment').find('.media__edit__comment').first();
    el__create.hide('fast'); // 답글쓰기의 전송하기 숨김
    el__edit.toggle('fast').end().find('textarea').first().focus();
});

//좋아요 기능
$(document).on('click', '.btn__vote__comment', function(e) {
    article_id = $(this).closest('.btn__vote__comment').data('id');
    var self = $(this),
    commentId = $(this).closest('.item__comment').data('id');
    console.log(article_id);
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        type: 'POST',
        url: '/comments/' + commentId + '/votes',
        data: {
            vote: self.data('vote')
        }
    }).then(function (data) {
        $(`.container__comment${article_id}`).load(`/articles/${article_id} .container__comment${article_id}`);
    });
});
</script>
@endsection

@section("style")
    <style>
    aside li {
        /* background-color: red; */
        margin-bottom:2%;
    }
    .button__list__articles {
        /* background-color: red; */
        border-radius: 0.25rem;
        border: 2px solid black;
    }
    .action__article button {
        padding: 10px 20px;
        margin: 10px;
    }

    .pull-left {
        padding: 3px 10px;
    }

    .form__new__comment {
        border-radius: 0.25rem;
        border: 2px solid rgba(0, 0, 0, 0.3);
        margin-bottom: 15px;
        padding: 5px;
    }
    .media__create__comment {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    /* 글 몸통 */
    .media-body {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    /* 좋아요 싫어요 이미지 버튼 */
    .btn__delete__comment, .btn__edit__comment, .btn__reply__comment {
        padding: 0 3px;
        margin: 3px 3px;
        border: 2px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.33rem;
        color:white;
        text-shadow: 0 0 5px black;
        background-image: linear-gradient(120deg, #89f7fe 0%, #66a6ff 100%);
    }
    .divdiv {
        margin: 2%;
    }

    .divdiv img {
        width: 100%;

    }

    </style>
@stop
