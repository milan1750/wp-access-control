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

  // Auto slug
  function bindScopeSlug() {
    $(document).on("input", "#wpac-scope-name", function () {
      let name = $(this).val();
      $("#wpac-scope-slug").val(name ? slugify(name) : "");
    });
  }

  // Checkbox logic
  function bindCheckboxLogic() {
    $("#wpac-global-checkbox").on("change", function () {
      const checked = $(this).is(":checked");
      $(".wpac-entity-checkbox, .wpac-site-checkbox").prop("checked", checked);
    });

    $(".wpac-entity-checkbox").on("change", function () {
      const entityId = $(this).data("entity");
      const checked = $(this).is(":checked");

      $(`.wpac-site-checkbox[data-entity="${entityId}"]`).prop(
        "checked",
        checked,
      );

      if (!checked) $("#wpac-global-checkbox").prop("checked", false);
      else {
        const allEntities = $(".wpac-entity-checkbox").length;
        const allChecked = $(".wpac-entity-checkbox:checked").length;
        if (allEntities === allChecked)
          $("#wpac-global-checkbox").prop("checked", true);
      }
    });

    $(".wpac-site-checkbox").on("change", function () {
      const entityId = $(this).data("entity");
      const allSites = $(
        `.wpac-site-checkbox[data-entity="${entityId}"]`,
      ).length;
      const checkedSites = $(
        `.wpac-site-checkbox[data-entity="${entityId}"]:checked`,
      ).length;

      $(`.wpac-entity-checkbox[data-entity="${entityId}"]`).prop(
        "checked",
        checkedSites === allSites,
      );
      const allEntities = $(".wpac-entity-checkbox").length;
      const allEntitiesChecked = $(".wpac-entity-checkbox:checked").length;
      const allSitesChecked =
        $(".wpac-site-checkbox").length ===
        $(".wpac-site-checkbox:checked").length;
      $("#wpac-global-checkbox").prop(
        "checked",
        allEntities === allEntitiesChecked && allSitesChecked,
      );
    });
  }

  function getScopeConfig() {
    let config = { global: false, entities: {} };
    config.global = $("#wpac-global-checkbox").is(":checked");

    $(".wpac-entity-checkbox").each(function () {
      const entityId = $(this).data("entity");
      const sites = [];
      $(this)
        .closest(".wpac-tree-item")
        .find(".wpac-site-checkbox:checked")
        .each(function () {
          sites.push($(this).data("site"));
        });
      if ($(this).is(":checked") || sites.length > 0)
        config.entities[entityId] = sites;
    });

    return config;
  }

  // Save / Update Scope
  function bindScopeSave() {
    $(document).on("click", "#wpac-save-scope", function () {
      let btn = $(this);
      let id = valSafe("#wpac-scope-id-hidden");
      let name = valSafe("#wpac-scope-name");
      let slug = valSafe("#wpac-scope-slug");
      let config = JSON.stringify(getScopeConfig());

      if (!name || !slug) {
        Swal.fire("Warning", "Scope name is required", "warning");
        return;
      }

      setLoading(btn, true);

      $.post(WPAC.ajax, {
        action: "wpac_save_scope",
        nonce: WPAC.nonce,
        id: id,
        name: name,
        slug: slug,
        config: config,
      })
        .done(function (res) {
          if (!res || !res.success) {
            Swal.fire("Error", res?.data || "Failed", "error");
            return;
          }
          Swal.fire(
            "Success",
            id ? "Scope updated" : "Scope added",
            "success",
          ).then(() => location.reload());
        })
        .fail(function () {
          Swal.fire("Error", "Server error", "error");
        })
        .always(() => setLoading(btn, false));
    });
  }

  // Edit scope
  function bindScopeEdit() {
    $(document).on("click", ".wpac-edit-scope", function () {
      let item = $(this).closest(".wpac-item");

      $("#wpac-scope-id-hidden").val(item.data("id"));
      $("#wpac-scope-name").val(item.data("name"));
      $("#wpac-scope-slug").val(item.data("slug"));

      // Reset everything first
      $(
        "#wpac-global-checkbox, .wpac-entity-checkbox, .wpac-site-checkbox",
      ).prop("checked", false);

      let configText = item.attr("data-config");

      if (configText) {
        try {
          const config = JSON.parse(configText);

          // Set sites
          if (config.entities) {
            Object.keys(config.entities).forEach((entityId) => {
              const sites = config.entities[entityId];

              sites.forEach((siteId) => {
                $(
                  `.wpac-site-checkbox[data-entity="${entityId}"][data-site="${siteId}"]`,
                ).prop("checked", true);
              });
            });
          }

          // ✅ IMPORTANT: Recalculate entity + global AFTER setting sites
          $(".wpac-site-checkbox").trigger("change");

          // Global (optional override if explicitly saved)
          if (config.global) {
            $("#wpac-global-checkbox").prop("checked", true);
          }
        } catch (e) {
          console.error("Invalid config JSON", e);
        }
      }

      $("#wpac-save-scope").text("Update Scope");
      $("#wpac-cancel-scope").show();
    });
  }

  // Cancel edit
  function bindScopeCancel() {
    $(document).on("click", "#wpac-cancel-scope", function () {
      $("#wpac-scope-id-hidden, #wpac-scope-name, #wpac-scope-slug").val("");
      $(
        "#wpac-global-checkbox, .wpac-entity-checkbox, .wpac-site-checkbox",
      ).prop("checked", false);
      $(this).hide();
      $("#wpac-save-scope").text("Add Scope");
    });
  }

  // Delete scope
  function bindScopeDelete() {
    $(document).on("click", ".wpac-delete-scope", function () {
      let btn = $(this);
      let item = btn.closest(".wpac-item");
      let id = item.data("id");

      Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete the scope!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
      }).then((result) => {
        if (!result.isConfirmed) return;

        $.post(WPAC.ajax, {
          action: "wpac_delete_scope",
          nonce: WPAC.nonce,
          id: id,
        })
          .done(function (res) {
            if (!res || !res.success) {
              Swal.fire("Error", res?.data || "Delete failed", "error");
              return;
            }
            Swal.fire("Deleted", "", "success").then(() => item.remove());
          })
          .fail(function () {
            Swal.fire("Error", "Server error", "error");
          });
      });
    });
  }

  // Init
  function init() {
    bindScopeSlug();
    bindCheckboxLogic();
    bindScopeSave();
    bindScopeEdit();
    bindScopeCancel();
    bindScopeDelete();
  }

  $(function () {
    init();
  });
})(jQuery);
