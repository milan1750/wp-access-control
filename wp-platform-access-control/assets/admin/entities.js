window.WPAC = window.WPAC || {};

(function($){

    /* =========================
       HELPER FUNCTIONS
    ========================== */
    function valSafe(selector){
        return ($(selector).val() || '').trim();
    }

    function setLoading(btn, state, text="Saving..."){
        if(!btn.length) return;
        if(state){
            btn.data("old", btn.text());
            btn.prop("disabled", true).text(text);
        } else {
            btn.prop("disabled", false).text(btn.data("old"));
        }
    }

    function slugify(text){
        return (text || "").toLowerCase().trim()
            .replace(/&/g, " and ")
            .replace(/[^a-z0-9]+/g, "_")
            .replace(/^_+|_+$/g, "");
    }

    /* =========================
       AUTO SLUG
    ========================== */
    $(document).on("input", "#wpac-entity-name", function(){
        let name = $(this).val();
        $("#wpac-entity-slug").val(name ? slugify(name) : "");
    });

    /* =========================
       SAVE / UPDATE ENTITY
    ========================== */
    $(document).on("click", "#wpac-save-entity", function(){
        let btn = $(this);
        let id = valSafe("#wpac-entity-id");
        let name = valSafe("#wpac-entity-name");
        let slug = valSafe("#wpac-entity-slug");
        let status = valSafe("#wpac-entity-status");

        if(!name || !slug){
            Swal.fire("Warning", "Entity name is required", "warning");
            return;
        }

        setLoading(btn, true);

        $.post(WPAC.ajax, {
            action: 'wpac_save_entity',
            nonce: WPAC.nonce,
            id: id,
            name: name,
            slug: slug,
            status: status
        })
        .done(function(res){
            if(res && res.success){
                Swal.fire("Success", id ? "Entity updated" : "Entity added", "success")
                     .then(()=> location.reload());
            } else {
                Swal.fire("Error", (res && res.data && res.data.message) || "Failed", "error");
            }
        })
        .fail(function(){
            Swal.fire("Error", "Server error", "error");
        })
        .always(function(){
            setLoading(btn, false);
        });
    });

    /* =========================
       EDIT ENTITY
    ========================== */
    $(document).on("click", ".wpac-edit-entity", function(){
        let item = $(this).closest(".wpac-item");

        $("#wpac-entity-id").val(item.data("id"));
        $("#wpac-entity-name").val(item.data("name"));
        $("#wpac-entity-slug").val(item.data("slug"));
        $("#wpac-entity-status").val(item.data("status"));

        $("#wpac-save-entity").text("Update Entity");
        $("#wpac-cancel-entity").show();
    });

    /* =========================
       CANCEL EDIT
    ========================== */
    $(document).on("click", "#wpac-cancel-entity", function(){
        $("#wpac-entity-id, #wpac-entity-name, #wpac-entity-slug").val("");
        $("#wpac-entity-status").val("1");
        $("#wpac-save-entity").text("Add Entity");
        $(this).hide();
    });

    /* =========================
       DELETE ENTITY
    ========================== */
    $(document).on("click", ".wpac-delete-entity", function(){
        let item = $(this).closest(".wpac-item");
        let id = item.data("id");

        if(!id){
            Swal.fire("Error", "Invalid delete request", "error");
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently delete the entity!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, delete"
        }).then(function(result){
            if(!result.isConfirmed) return;

            $.post(WPAC.ajax, { action: "wpac_delete_entity", nonce: WPAC.nonce, id: id })
            .done(function(res){
                if(res && res.success){
                    Swal.fire("Deleted","Entity removed","success")
                         .then(()=> item.remove());
                } else {
                    Swal.fire("Error", (res && res.data && res.data.message) || "Delete failed","error");
                }
            })
            .fail(function(){
                Swal.fire("Error", "Server error", "error");
            });
        });
    });

})(jQuery);
