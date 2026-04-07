window.WPAC = window.WPAC || {};

(function ($) {
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
      .toLowerCase()
      .trim()
      .replace(/&/g, " and ")
      .replace(/[^a-z0-9]+/g, "_")
      .replace(/^_+|_+$/g, "");
  }

  function bindRoleSlug() {
    $(document).on("input", "#wpac-role-name", function () {
      let name = $(this).val();
      $("#wpac-role-slug").val(name ? slugify(name) : "");
    });
  }

  function bindModuleCheckboxes() {
    $(document).on("change", ".wpac-module-checkbox", function () {
      let module = $(this).data("module");
      $(".wpac-capability-checkbox[data-module='" + module + "']").prop(
        "checked",
        $(this).is(":checked"),
      );
    });
    $(document).on("change", ".wpac-capability-checkbox", function () {
      let module = $(this).data("module");
      let all = $(
        ".wpac-capability-checkbox[data-module='" + module + "']",
      ).length;
      let checked = $(
        ".wpac-capability-checkbox[data-module='" + module + "']:checked",
      ).length;
      $(".wpac-module-checkbox[data-module='" + module + "']").prop(
        "checked",
        all === checked,
      );
    });
  }

  function bindRoleSave() {
    $(document).on("click", "#wpac-save-role", function () {
      let btn = $(this);
      let id = valSafe("#wpac-role-id"),
        name = valSafe("#wpac-role-name"),
        slug = valSafe("#wpac-role-slug");
      if (!name || !slug) {
        Swal.fire("Warning", "Role name and slug required", "warning");
        return;
      }

      let caps = [];
      $(".wpac-capability-checkbox:checked").each(function () {
        caps.push($(this).val());
      });

      setLoading(btn, true);
      $.post(WPAC.ajax, {
        action: "wpac_save_role",
        nonce: WPAC.nonce,
        id: id,
        name: name,
        slug: slug,
        capabilities: caps,
      })
        .done(function (res) {
          if (!res || !res.success) {
            Swal.fire("Error", res?.data || "Failed", "error");
            return;
          }
          Swal.fire(
            "Success",
            id ? "Role updated" : "Role added",
            "success",
          ).then(() => location.reload());
        })
        .fail(function () {
          Swal.fire("Error", "Server error", "error");
        })
        .always(() => setLoading(btn, false));
    });
  }

 function bindRoleEdit() {
  $(document).on("click", ".wpac-edit-role", function () {
    let item = $(this).closest(".wpac-item");
    let id = item.data("id"),
        name = item.data("name"),
        slug = item.data("slug");

    // Safely parse caps, default to empty array
    let caps = [];
	console.log(item.data("caps"));

	try {
	caps = item.data("caps") || [];
	if (!Array.isArray(caps)) caps = [];
	} catch (e) {
	caps = [];
	console.warn("Invalid caps data for role ID:", id, e);
	}

    $("#wpac-role-id").val(id);
    $("#wpac-role-name").val(name);
    $("#wpac-role-slug").val(slug);

    // Reset all checkboxes
    $(".wpac-capability-checkbox, .wpac-module-checkbox").prop("checked", false);

    // Check assigned capabilities
    caps.forEach((c) => {
      $(".wpac-capability-checkbox[value='" + c + "']")
        .prop("checked", true)
        .trigger("change");
    });

    $("#wpac-save-role").text("Update Role");
    $("#wpac-cancel-role").show();
  });
}

  function bindRoleCancel() {
    $(document).on("click", "#wpac-cancel-role", function () {
      $("#wpac-role-id,#wpac-role-name,#wpac-role-slug").val("");
      $(".wpac-capability-checkbox,.wpac-module-checkbox").prop(
        "checked",
        false,
      );
      $("#wpac-save-role").text("Add Role");
      $(this).hide();
    });
  }

  function bindRoleDelete() {
    $(document).on("click", ".wpac-delete-role", function () {
      let btn = $(this),
        item = btn.closest(".wpac-item"),
        id = item.data("id");
      Swal.fire({
        title: "Are you sure?",
        text: "This cannot be undone",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
      }).then((res) => {
        if (!res.isConfirmed) return;
        $.post(WPAC.ajax, {
          action: "wpac_delete_role",
          nonce: WPAC.nonce,
          id: id,
        })
          .done((res) => {
            if (!res || !res.success) {
              Swal.fire("Error", res?.data || "Delete failed", "error");
              return;
            }
            Swal.fire("Deleted", "", "success").then(() => item.remove());
          })
          .fail(() => Swal.fire("Error", "Server error", "error"));
      });
    });
  }

  function init() {
    bindRoleSlug();
    bindModuleCheckboxes();
    bindRoleSave();
    bindRoleEdit();
    bindRoleCancel();
    bindRoleDelete();
  }
  $(function () {
    init();
  });
})(jQuery);
