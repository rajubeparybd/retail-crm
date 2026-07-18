---
name: application-feature
description: Build Laravel applications using artisan commands with backend-first approach. Use Actions for business logic, events/listeners for decoupling, observers for model events, queues for async processing, mail for emails, schedules for cron tasks. Activate when creating Laravel features, models, events, jobs, mail, schedules, or backend business logic.
---

# Laravel Application Building

Build Laravel applications following Laravel conventions with backend-first architecture. Actions handle business logic, controllers remain thin.

## Core Principles

**Backend-first approach**: Implement business logic in Actions, not controllers. Controllers validate requests and delegate to Actions.

**Use Artisan commands**: All components created via `php artisan make:` - models, events, listeners, observers, jobs, mail, commands, etc.

**Decouple with events**: Use events/listeners for side effects (notifications, logging, analytics) to keep core logic clean.

## Quick Reference

| Feature  | Artisan Command             | Location                |
| -------- | --------------------------- | ----------------------- |
| Model    | `php artisan make:model`    | `app/Models/`           |
| Action   | `php artisan make:class`    | `app/Actions/`          |
| Event    | `php artisan make:event`    | `app/Events/`           |
| Listener | `php artisan make:listener` | `app/Listeners/`        |
| Observer | `php artisan make:observer` | `app/Observers/`        |
| Job      | `php artisan make:job`      | `app/Jobs/`             |
| Mail     | `php artisan make:mail`     | `app/Mail/`             |
| Command  | `php artisan make:command`  | `app/Console/Commands/` |

## Typical Workflow

1. Create models with factories and seeders: `php artisan make:model --factory --seed`
2. Create Actions for business logic: `php artisan make:class`
3. Use events/listeners for decoupled side effects
4. Queue slow operations via Jobs
5. Send emails via Mailable classes
6. Schedule tasks via Console Commands

## References

- **Events & Listeners**: See [events-listeners.md](references/events-listeners.md)
- **Observers**: See [observers.md](references/observers.md)
- **Queues**: See [queues.md](references/queues.md)
- **Mail**: See [mail.md](references/mail.md)
- **Schedules**: See [schedules.md](references/schedules.md)
- **Actions**: See [actions.md](references/actions.md)
- **Models**: See [models.md](references/models.md)
