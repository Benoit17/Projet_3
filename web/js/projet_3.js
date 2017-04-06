jQuery(document).ready(function($){

    $('.reply').click(function(e){
        e.preventDefault();
        var $form = $('#form-comment');
        var $this = $(this);
        var com_id = $this.data('id');
        var $comment = $('#comment-' + com_id);

        $form.find('h3').text('Répondre à ce commentaire');
        $('#parent_id').val(parent_id);
        $comment.after($form);
    })
});