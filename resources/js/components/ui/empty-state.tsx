import * as React from "react"

import { cn } from "@/lib/utils"

function EmptyState({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-state"
      className={cn(
        "flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed p-10 text-center",
        className
      )}
      {...props}
    />
  )
}

function EmptyStateIcon({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-state-icon"
      className={cn(
        "text-muted-foreground [&>svg]:size-10 [&>svg]:shrink-0",
        className
      )}
      {...props}
    />
  )
}

function EmptyStateTitle({ className, ...props }: React.ComponentProps<"h3">) {
  return (
    <h3
      data-slot="empty-state-title"
      className={cn("text-lg font-semibold", className)}
      {...props}
    />
  )
}

function EmptyStateDescription({
  className,
  ...props
}: React.ComponentProps<"p">) {
  return (
    <p
      data-slot="empty-state-description"
      className={cn("text-muted-foreground max-w-sm text-sm", className)}
      {...props}
    />
  )
}

function EmptyStateAction({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="empty-state-action"
      className={cn("mt-2", className)}
      {...props}
    />
  )
}

export {
  EmptyState,
  EmptyStateIcon,
  EmptyStateTitle,
  EmptyStateDescription,
  EmptyStateAction,
}
