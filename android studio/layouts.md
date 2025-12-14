# Layouts


## ConstraintLayout (recommended for modern apps)



```xml

<androidx.constraintlayout.widget.ConstraintLayout
    xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    android:layout_width="match_parent"
    android:layout_height="match_parent">

    <!-- TextView centered at the top -->
    <TextView
        android:id="@+id/title"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Money Tracker"
        android:textSize="24sp"
        app:layout_constraintTop_toTopOf="parent"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"
        android:layout_marginTop="32dp"/>

    <!-- Input field below the title -->
    <EditText
        android:id="@+id/amountInput"
        android:layout_width="0dp"
        android:layout_height="wrap_content"
        android:hint="Enter amount"
        app:layout_constraintTop_toBottomOf="@id/title"
        app:layout_constraintStart_toStartOf="parent"
        app:layout_constraintEnd_toEndOf="parent"
        android:layout_marginTop="16dp"
        android:layout_marginStart="16dp"
        android:layout_marginEnd="16dp"/>

    <!-- Button below the input -->
    <Button
        android:id="@+id/addButton"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Add"
        app:layout_constraintTop_toBottomOf="@id/amountInput"
        app:layout_constraintEnd_toEndOf="@id/amountInput"
        android:layout_marginTop="16dp"/>

</androidx.constraintlayout.widget.ConstraintLayout>

```

#### android:layout_width="0dp" in ConstraintLayout
- In ConstraintLayout, when you set width (or height) to 0dp, it does NOT literally mean "0 pixels".
- Instead, it means “match constraints”, i.e., the view will expand to fill the space between the constraints.

#### Positioning Elements
- in ConstraintLayout, these are usually required to position the view.
- `app:layout_constraintTop_toTopOf="parent"` position top of element to be top of parent
- `app:layout_constraintStart_toStartOf="parent"` position start (left) of element to start of parent
- `app:layout_constraintEnd_toEndOf="parent"` position end of element (right) to end of parent
- `app:layout_constraintTop_toBottomOf="@id/amountInput"` position top of element to be bottom of amountInput element