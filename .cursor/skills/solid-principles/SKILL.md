---
name: solid-principles
description: >-
  Apply SOLID principles to this PHP app. Use when adding or changing
  application code, controllers, models, routes, or views, or when the user
  mentions SOLID, single responsibility, open/closed, Liskov, interface
  segregation, or dependency inversion.
---

# SOLID in this project

Apply these rules to PHP under `app/` and `index.php`. The agent already knows the five principles. Use the mapping below. Do not refactor unrelated code to demonstrate a principle, and do not add a class, interface, or base type that has only one caller.

`index.php` is the composition root. It requires each class file directly. There is no PSR-4 autoload. A new class must be required there, constructed there, and passed into the object that uses it.

Views render variables they are given. They do not query, send mail, start sessions, or redirect.

## Single responsibility

Give each class one reason to change.

- A controller checks auth, chooses a view, and sets the HTTP response. SQL, mail, and session rules belong in a model.
- A model does one job. `Auth` owns the session. `EnquiryMailer` sends one enquiry. A new table gets its own model, not another query method on `DashboardController`.
- A view file renders one screen. Do not put a second screen's markup in it.

## Open/closed

Add behavior by adding a type or a route entry. Do not rewrite a working path to carry an unrelated case.

- A new dashboard section is a new view directory plus one entry in the page map inside `DashboardController::show`. Leave the other entries alone.
- A new public route is a new branch in `index.php` that constructs its controller. Do not grow `HomeController::handle` with unrelated paths.
- A new kind of mail or storage is a new model. Do not add a flag to `EnquiryMailer` that switches it into a different job.

## Liskov substitution

This app has no class hierarchy. Do not introduce a parent class so that subclasses can share a few lines.

If a second implementation is actually required, it must accept the same inputs and keep the same outcomes as the caller already relies on: return type, exceptions, and side effects. `Auth::check` stays a boolean session check. `EnquiryMailer::send` stays a boolean send result.

## Interface segregation

Do not add an interface for a single implementation. Pass the collaborator the caller already uses, as `AuthController` takes `Auth` and `HomeController` takes `EnquiryMailer`.

When a constructor needs a collaborator, typehint the methods that constructor's class calls. Do not hand a view, a controller, or `Database` to a model that only needs a `PDO` or a mail result.

## Dependency inversion

Construct collaborators in `index.php` and pass them through constructors. Do not call `Database::connection()` from a view or from a new method deep in a controller.

New database access lives in a model. The model receives the `PDO` from `Database::connection()` at the composition root, or from the controller constructor if that controller is the only edge already allowed to see it. Keep that call on the edge that creates the model, not scattered through screens.

## When not to apply

Leave CSS, markup-only copy, Docker, and skills alone. During a code review, mention a SOLID break only when it is a defect or the user asked for a design review. Do not file a review finding that only prefers a different shape.
