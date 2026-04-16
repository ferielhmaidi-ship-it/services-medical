module com.tabibnet.calendar {
    requires javafx.controls;
    requires javafx.fxml;
    requires java.sql;

    opens com.tabibnet.calendar to javafx.fxml;

    exports com.tabibnet.calendar;

    opens com.tabibnet.calendar.controller to javafx.fxml;

    exports com.tabibnet.calendar.controller;

    opens com.tabibnet.calendar.model to javafx.base;

    exports com.tabibnet.calendar.model;
}
