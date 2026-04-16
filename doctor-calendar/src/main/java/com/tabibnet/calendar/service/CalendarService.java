package com.tabibnet.calendar.service;

import com.tabibnet.calendar.model.CalendarSetting;
import com.tabibnet.calendar.model.Indisponibilite;
import com.tabibnet.calendar.model.TempsTravail;
import com.tabibnet.calendar.model.TimeSlot;

import java.time.LocalDate;
import java.time.LocalTime;
import java.util.ArrayList;
import java.util.List;

public class CalendarService {

    public List<TimeSlot> generateSlots(LocalDate date, TempsTravail tempsTravail, CalendarSetting setting, List<Indisponibilite> indisponibilites) {
        List<TimeSlot> slots = new ArrayList<>();

        // Check if the doctor is completely unavailable this day
        if (indisponibilites != null) {
            for (Indisponibilite i : indisponibilites) {
                if (i.getDate().equals(date)) {
                    // Return empty list or a list with a message. Here, just return empty list.
                    return slots;
                }
            }
        }

        if (tempsTravail == null || setting == null) {
            return slots;
        }

        LocalTime currentStartTime = tempsTravail.getStartTime();
        LocalTime endTime = tempsTravail.getEndTime();
        int duration = setting.getSlotDuration();

        LocalTime pauseStart = setting.getPauseStart();
        LocalTime pauseEnd = setting.getPauseEnd();

        while (currentStartTime.plusMinutes(duration).compareTo(endTime) <= 0) {
            LocalTime slotEndTime = currentStartTime.plusMinutes(duration);

            boolean isPause = false;
            if (pauseStart != null && pauseEnd != null) {
                // If slot overlaps with pause time
                if ((currentStartTime.compareTo(pauseStart) >= 0 && currentStartTime.compareTo(pauseEnd) < 0) ||
                    (slotEndTime.compareTo(pauseStart) > 0 && slotEndTime.compareTo(pauseEnd) <= 0) || 
                    (currentStartTime.compareTo(pauseStart) <= 0 && slotEndTime.compareTo(pauseEnd) >= 0)) {
                    isPause = true;
                }
            }

            if (!isPause) {
                slots.add(new TimeSlot(currentStartTime, slotEndTime, true));
            } else {
                // We shouldn't add it as an available time slot, but possibly display a pause block later in UI
                // So skip it or mark false. We'll skip adding it to available slots.
            }

            currentStartTime = slotEndTime;
        }

        return slots;
    }
}
