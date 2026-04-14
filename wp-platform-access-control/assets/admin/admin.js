window.WPAC = window.WPAC || {};

(function ($) {
  /* =========================
HELPERS
========================== */

  function setLoading(btn, state, text = "Saving...") {
    if (!btn || !btn.length) return;

    if (state) {
      btn.data("old", btn.text());
      btn.prop("disabled", true).text(text);
    } else {
      btn.prop("disabled", false).text(btn.data("old"));
    }
  }

  function valSafe(selector) {
    return ($(selector).val() || "").toString().trim();
  }

  function slugify(text) {
    return (text || "")
      .toString()
      .toLowerCase()
      .trim()
      .replace(/&/g, " and ")
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/-+/g, "-")
      .replace(/(^-|-$)/g, "");
  }

  /* =========================
ENTITY SLUG AUTO
========================== */
  function bindEntitySlug() {
    $(document)
      .off("input.wpacEntitySlug")
      .on("input.wpacEntitySlug", "#wpac-entity-name", function () {
        let value = $(this).val();
        $("#wpac-entity-slug").val(value ? slugify(value) : "");
      });
  }

  /* =========================
ENTITY SAVE (CREATE + UPDATE)
========================== */
  function bindEntitySave() {
    $(document).on("click", "#wpac-save", function (e) {
      e.preventDefault();

      let btn = $(this);

      let id = valSafe("#wpac-entity-id");
      let name = valSafe("#wpac-entity-name");
      let slug = valSafe("#wpac-entity-slug");
      let status = valSafe("#wpac-entity-status");

      if (!name) {
        Swal.fire("Warning", "Entity name required", "warning");
        return;
      }

      setLoading(btn, true, "Saving...");

      $.post(WPAC.ajax, {
        action: "wpac_save_entity",
        nonce: WPAC.nonce,
        id,
        name,
        slug,
        status,
      })

        .done((res) => {
          if (!res || !res.success) {
            Swal.fire("Error", res?.data || "Failed", "error");
            return;
          }

          Swal.fire({
            icon: "success",
            title: id ? "Entity updated" : "Entity added",
            timer: 1200,
            showConfirmButton: false,
          });

          setTimeout(() => location.reload(), 1200);
        })

        .fail((xhr) => {
          console.error(xhr.responseText);
          Swal.fire("Error", "Server error", "error");
        })

        .always(() => {
          setLoading(btn, false);
        });
    });
  }

  /* =========================
ENTITY EDIT
========================== */
  function bindEntityEdit() {
    $(document)
      .off("click.wpacEntityEdit")
      .on("click.wpacEntityEdit", ".wpac-edit", function () {
        let item = $(this).closest(".wpac-item");

        $("#wpac-entity-id").val(item.data("id"));
        $("#wpac-entity-name").val(item.data("name"));
        $("#wpac-entity-slug").val(item.data("slug"));
        $("#wpac-entity-status").val(item.data("status"));

        $("#wpac-form-title").text("Edit Entity");
        $("#wpac-save").text("Update Entity");
        $("#wpac-cancel").show();

        $("html, body").animate({ scrollTop: 0 }, 200);
      });
  }

  /* =========================
ENTITY CANCEL
========================== */
  function bindEntityCancel() {
    $(document)
      .off("click.wpacEntityCancel")
      .on("click.wpacEntityCancel", "#wpac-cancel", function () {
        $("#wpac-entity-id").val("");
        $("#wpac-entity-name").val("");
        $("#wpac-entity-slug").val("");
        $("#wpac-entity-status").val("1");

        $("#wpac-form-title").text("Create Entity");
        $("#wpac-save").text("Save Entity");
        $("#wpac-cancel").hide();
      });
  }

  /* =========================
ENTITY MANAGE
========================== */
  function bindEntityManage() {
    $(document)
      .off("click.wpacEntityManage")
      .on("click.wpacEntityManage", ".wpac-manage", function () {
        let id = $(this).closest(".wpac-item").data("id");
        if (!id) return;

        window.location.href = "admin.php?page=wpac-entity-manage&id=" + id;
      });
  }

  /* =========================
ENTITY DELETE
========================== */
  function bindEntityDelete() {
    $(document)
      .off("click.wpacDelete")
      .on("click.wpacDelete", ".wpac-delete", function (e) {
        e.preventDefault();

        let btn = $(this);
        let item = btn.closest(".wpac-item");

        let id = btn.data("id");
        let type = btn.data("type");

        if (!id || type !== "entity") {
          Swal.fire("Error", "Invalid delete request", "error");
          return;
        }

        Swal.fire({
          title: "Are you sure?",
          text: "This cannot be undone",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          confirmButtonText: "Yes, delete",
        }).then((result) => {
          if (!result.isConfirmed) return;

          $.post(WPAC.ajax, {
            action: "wpac_delete_entity",
            nonce: WPAC.nonce,
            id: id,
          })

            .done((res) => {
              if (!res || !res.success) {
                Swal.fire("Error", res?.data || "Delete failed", "error");
                return;
              }

              Swal.fire({
                icon: "success",
                title: "Deleted",
                timer: 1000,
                showConfirmButton: false,
              });

              item.fadeOut(200, function () {
                $(this).remove();
              });
            })

            .fail((xhr) => {
              console.error(xhr.responseText);
              Swal.fire("Error", "Server error", "error");
            });
        });
      });
  }

      /* =========================
    SITE SAVE (CREATE + UPDATE)
    ========================== */
    function bindSiteSave() {
        $(document).on("click", "#wpac-add-site", function(e) {
            e.preventDefault();

            let btn = $(this);
            let id        = valSafe("#wpac-site-id-hidden"); // hidden for edit
            let entity_id = valSafe("#wpac-site-entity");
            let site_id   = valSafe("#wpac-site-id");
            let name      = valSafe("#wpac-site-name");
            let address  = valSafe("#wpac-site-location");

            if (!entity_id || !site_id || !name) {
                Swal.fire("Warning", "Entity, Site ID, and Name are required", "warning");
                return;
            }

            setLoading(btn, true, id ? "Updating..." : "Saving...");

            $.post(WPAC.ajax, {
                action: "wpac_save_site",
                nonce: WPAC.nonce,
                id,
                entity_id,
                site_id,
                name,
                location:address
            })
            .done(res => {
                if (!res || !res.success) {
                    Swal.fire("Error", res?.data || "Failed", "error");
                    return;
                }

                Swal.fire({
                    icon: "success",
                    title: id ? "Site updated" : "Site added",
                    timer: 1200,
                    showConfirmButton: false
                });

          		setTimeout(() => location.reload(), 1200);
            })
            .fail(xhr => {
                console.error(xhr.responseText);
                Swal.fire("Error", "Server error", "error");
            })
            .always(() => setLoading(btn, false));
        });
    }

    /* =========================
    SITE EDIT
    ========================== */
    function bindSiteEdit() {
        $(document).on("click", ".wpac-edit-site", function() {
            let item = $(this).closest(".wpac-item");

            $("#wpac-site-id-hidden").val(item.data("id")); // hidden field for update
            $("#wpac-site-entity").val(item.data("entity"));
            $("#wpac-site-id").val(item.data("siteid"));
            $("#wpac-site-name").val(item.data("name"));
            $("#wpac-site-location").val(item.data("location"));

            $("#wpac-add-site").text("Update Site");
            $("#wpac-cancel-site").show();

            $("html, body").animate({ scrollTop: 0 }, 200);
        });
    }

    /* =========================
    SITE CANCEL EDIT
    ========================== */
    function bindSiteCancel() {
        $(document).on("click", "#wpac-cancel-site", function() {
            $("#wpac-site-id-hidden").val("");
            $("#wpac-site-entity").val("");
            $("#wpac-site-id").val("");
            $("#wpac-site-name").val("");
            $("#wpac-site-location").val("");

            $("#wpac-add-site").text("Add Site");
            $(this).hide();
        });
    }

    /* =========================
    SITE DELETE
    ========================== */
    function bindSiteDelete() {
        $(document).on("click", ".wpac-delete", function(e) {
            e.preventDefault();

            let btn = $(this);
            let item = btn.closest(".wpac-item");
            let id = btn.data("id");
            let type = btn.data("type");

            if (!id || type !== "site") {
                Swal.fire("Error", "Invalid delete request", "error");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "This cannot be undone",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, delete"
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post(WPAC.ajax, {
                    action: "wpac_delete_site",
                    nonce: WPAC.nonce,
                    id: id
                })
                .done(res => {
                    if (!res || !res.success) {
                        Swal.fire("Error", res?.data || "Delete failed", "error");
                        return;
                    }

                    Swal.fire({
                        icon: "success",
                        title: "Deleted",
                        timer: 1000,
                        showConfirmButton: false
                    });

                    item.fadeOut(200, function() { $(this).remove(); });
                })
                .fail(xhr => {
                    console.error(xhr.responseText);
                    Swal.fire("Error", "Server error", "error");
                });
            });
        });
    }

	/* =========================
   ROLE SLUG AUTO
========================= */
function bindRoleSlug() {
    $(document).on("input", "#wpac-role-name", function() {
        let value = $(this).val();
        $("#wpac-role-slug").val(value ? slugify(value) : "");
    });
}

/* =========================
   ROLE EDIT
========================= */
function bindRoleEdit() {
    $(document).on("click", ".wpac-edit-role", function() {
        let btn = $(this);
        let id   = btn.data("id");
        let name = btn.data("name");
        let slug = btn.data("slug");

        $("#wpac-role-name").val(name);
        $("#wpac-role-slug").val(slug);
        $("#wpac-save-role").text("Update Role").data("id", id);
        $("#wpac-cancel-role").show();
    });
}

/* =========================
   ROLE SAVE (CREATE + UPDATE)
========================= */
function bindRoleSave() {
    $(document).on("click", "#wpac-save-role", function(e) {
        e.preventDefault();
        let btn = $(this);
        let id  = btn.data("id") || 0;

        let name = valSafe("#wpac-role-name");
        let slug = valSafe("#wpac-role-slug");

        if (!name || !slug) {
            Swal.fire("Warning", "Role name and slug required", "warning");
            return;
        }

        setLoading(btn, true);

        $.post(WPAC.ajax, {
            action: id ? "wpac_save_role" : "wpac_save_role",
            nonce: WPAC.nonce,
            id: id,
            name: name,
            slug: slug
        })
        .done(function(res) {
            if (!res || !res.success) {
                Swal.fire("Error", res?.data || "Failed", "error");
                return;
            }
            Swal.fire("Success", id ? "Role updated" : "Role added", "success")
                .then(() => location.reload());
        })
        .fail(function(xhr) {
            console.error(xhr.responseText);
            Swal.fire("Error", "Server error", "error");
        })
        .always(function() {
            setLoading(btn, false);
        });
    });
}

/* =========================
   ROLE DELETE
========================= */
function bindRoleDelete() {
    $(document).on("click", ".wpac-delete-role", function(e) {
        e.preventDefault();

        let btn = $(this);
        let id  = btn.data("id");

        if (!id) {
            Swal.fire("Error", "Invalid delete request", "error");
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "This cannot be undone",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, delete"
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post(WPAC.ajax, {
                action: "wpac_delete_role",
                nonce: WPAC.nonce,
                id: id
            })
            .done(res => {
                if (!res || !res.success) {
                    Swal.fire("Error", res?.data || "Delete failed", "error");
                    return;
                }
                Swal.fire({ icon: "success", title: "Deleted", timer: 1000, showConfirmButton: false });
                btn.closest(".wpac-item").fadeOut(200, function() { $(this).remove(); });
            })
            .fail(xhr => {
                console.error(xhr.responseText);
                Swal.fire("Error", "Server error", "error");
            });
        });
    });
}

/* =========================
   ROLE CANCEL
========================= */
function bindRoleCancel() {
    $(document).on("click", "#wpac-cancel-role", function() {
        $("#wpac-role-name").val("");
        $("#wpac-role-slug").val("");
        $("#wpac-save-role").text("Add Role").removeData("id");
        $(this).hide();
    });
}

  /* =========================
INIT (ENTITY ONLY)
========================== */
  function init() {
    bindEntitySlug();
    bindEntitySave();
    bindEntityEdit();
    bindEntityCancel();
    bindEntityManage();
    bindEntityDelete();
	bindSiteSave();
	bindSiteEdit();
	bindSiteCancel();
	bindSiteDelete();
	bindRoleSave();
	bindRoleEdit();
	bindRoleCancel();
	bindRoleDelete();
  }

  $(function () {
    init();
  });
})(jQuery);
